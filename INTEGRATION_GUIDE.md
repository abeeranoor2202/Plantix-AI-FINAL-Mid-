# Plantix AI - Flask & Laravel Integration Guide

## Overview

This document describes the complete integration between:
- **Laravel Backend** (Admin Panel) at `D:\FYP-FINAL(MID)\Admin Panel`
- **Flask Crop Prediction API** at `D:\FYP-FINAL(MID)\crop_prediction_api`
- **AI Modules** (Training & Models) at `D:\FYP-FINAL(MID)\AIModules`

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                   Frontend (React/Vue)                      │
├─────────────────────────────────────────────────────────────┤
│ http://localhost:3000 or http://localhost:5173             │
└──────────────────────────┬──────────────────────────────────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
        ▼                  ▼                  ▼
┌──────────────────┐ ┌──────────────────┐ ┌──────────────────┐
│  Laravel API     │ │  Flask API       │ │  Database        │
│  :8000           │ │  :5000           │ │  MySQL           │
├──────────────────┤ ├──────────────────┤ ├──────────────────┤
│ • Auth           │ │ • Prediction     │ │ • Users          │
│ • Business Logic │ │ • Model Loading  │ │ • Crops          │
│ • Webhooks       │ │ • Logging        │ │ • Predictions    │
└────────┬─────────┘ └────────┬─────────┘ └──────────────────┘
         │                    │
         └────────────────────┘
         HTTP/JSON Communication
         API Key Authentication
```

## Environment Configuration

### Laravel (.env)

Located: `Admin Panel\.env`

```env
# Flask Crop Prediction API Configuration
CROP_PREDICTION_API_URL=http://127.0.0.1:5000
CROP_PREDICTION_API_KEY=replace-with-a-long-random-api-key
CROP_PREDICTION_API_TIMEOUT=10
```

Key points:
- `CROP_PREDICTION_API_URL`: Must match Flask server address
- `CROP_PREDICTION_API_KEY`: Must match Flask `API_KEY` environment variable
- `CROP_PREDICTION_API_TIMEOUT`: Request timeout in seconds (default: 10)

### Flask (.env)

Located: `crop_prediction_api\.env`

```env
# Authentication
API_KEY=replace-with-a-long-random-api-key
API_KEY_HEADER=X-API-Key

# Model & Database
MODEL_PATH=./model/RandomForest.pkl
DATABASE_PATH=./instance/predictions.sqlite3

# CORS (allow Laravel)
ALLOWED_ORIGINS=http://localhost:3000,http://localhost:5173,http://localhost:8000

# Integration with Laravel
LARAVEL_WEBHOOK_URL=http://localhost:8000/api/webhooks/predictions
```

Key points:
- Include `http://localhost:8000` in `ALLOWED_ORIGINS` to allow Laravel requests
- `API_KEY` must match Laravel's `CROP_PREDICTION_API_KEY`

## Setup Instructions

### Step 1: Update Environment Variables

1. **Laravel Setup**
   ```bash
   cd "Admin Panel"
   cp .env.example .env
   # Edit .env with your settings
   ```

2. **Flask Setup**
   ```bash
   cd crop_prediction_api
   cp .env.example .env
   # Edit .env with your settings, ensuring API_KEY matches Laravel config
   ```

### Step 2: Database Migrations (Laravel)

```bash
cd "Admin Panel"

# Run migrations to create prediction_logs table
php artisan migrate

# Verify database
php artisan tinker
> DB::table('prediction_logs')->count()
```

### Step 3: Start Services

**Terminal 1 - Laravel**
```bash
cd "Admin Panel"
php artisan serve --port=8000
```

**Terminal 2 - Flask**
```bash
cd crop_prediction_api
# Activate virtual environment first, then:
python run.py
# Or with specific port:
FLASK_ENV=production python -m flask run --port=5000
```

### Step 4: Verify Integration

Run the integration test:

```bash
cd "Admin Panel"
php artisan tinker

# Test health check
>>> Http::get('http://127.0.0.1:5000/health')->json();

# Test model info
>>> Http::get('http://127.0.0.1:5000/model-info')->json();

# Test prediction
>>> Http::withHeader('X-API-Key', env('CROP_PREDICTION_API_KEY'))
    ->post('http://127.0.0.1:5000/predict', [
        'nitrogen' => 90,
        'phosphorus' => 42,
        'potassium' => 43,
        'temperature' => 22.5,
        'humidity' => 82,
        'ph' => 6.5,
        'rainfall' => 200,
    ])->json();
```

## API Endpoints

### Flask API Endpoints

All endpoints require `X-API-Key` header (except health check).

#### Public Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/health` | Health check (no auth required) |
| GET | `/model-info` | Model information |
| POST | `/predict` | Make crop prediction |

#### Admin Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/stats` | Prediction statistics |
| GET | `/admin/predictions?limit=100&offset=0` | Prediction history |

### Laravel Integration Points

1. **CropPredictionService** (`app/Services/CropPredictionService.php`)
   - Handles HTTP communication with Flask API
   - Manages API key authentication
   - Retry logic and error handling

2. **CropRecommendationService** (`app/Services/Customer/CropRecommendationService.php`)
   - Uses `CropPredictionService` to get predictions
   - Creates `CropRecommendation` records
   - Logs to `PredictionLog` table

3. **CustomerAiApiController** (`app/Http/Controllers/Api/CustomerAiApiController.php`)
   - `/api/customer/ai/crop-recommendation` - POST endpoint for crop recommendations

## Database Schema

### crop_recommendations table
- Core prediction results stored here
- Links to users and soil tests
- JSON array of recommended crops with confidence scores

### prediction_logs table (New)
- Audit log of all predictions
- Tracks individual predictions with input parameters
- Stores success/failure status
- Used for analytics and debugging
- Indexes on `user_id`, `predicted_crop`, `status`, `predicted_at`

## Data Flow

### User Makes Prediction

```
1. Frontend sends POST /api/customer/ai/crop-recommendation
   ├─ Payload: {nitrogen, phosphorus, potassium, ph_level, humidity, rainfall_mm, temperature}
   
2. CustomerAiApiController receives request
   ├─ Validates input
   ├─ Calls CropRecommendationService->recommend()
   
3. CropRecommendationService calls CropPredictionService->predict()
   ├─ Prepares data (maps ph_level → ph, rainfall_mm → rainfall)
   ├─ Makes HTTP POST to Flask /predict endpoint
   ├─ Adds X-API-Key authentication header
   
4. Flask API processes prediction
   ├─ Validates input against feature ranges
   ├─ Loads model if not already loaded
   ├─ Generates prediction
   ├─ Stores in local SQLite database
   ├─ Returns prediction with metadata (crop, confidence, request_id, etc.)
   
5. CropRecommendationService processes response
   ├─ Creates CropRecommendation record
   ├─ Creates PredictionLog record (for analytics)
   ├─ Returns to Controller
   
6. Controller returns JSON response to Frontend
   ├─ Status: 200/201
   ├─ Body: {crop, confidence, request_id, ...}
```

## Error Handling

### Common Issues

1. **Connection Refused**
   - Ensure Flask is running on correct port (default: 5000)
   - Check `CROP_PREDICTION_API_URL` in Laravel .env

2. **401 Unauthorized**
   - Verify API keys match in both `.env` files
   - Check `X-API-Key` header is being sent

3. **Model Loading Failed**
   - Verify `MODEL_PATH` in Flask .env points to valid `.pkl` file
   - Check file permissions

4. **Timeout**
   - Increase `CROP_PREDICTION_API_TIMEOUT` in Laravel .env
   - Check Flask server is not overloaded

### Logging

**Laravel Logs:** `Admin Panel/storage/logs/laravel.log`
```
[2024-04-21] ERROR: Crop recommendation inference failed.
- user_id: 1
- message: Connection refused...
```

**Flask Logs:** `crop_prediction_api/logs/`
- Access logs for all requests
- Error logs for failed predictions
- Model loading information

## Testing

### Unit Tests
```bash
cd "Admin Panel"
php artisan test tests/Feature/CropRecommendationTest.php
```

### Integration Test
```bash
cd "Admin Panel"
php tests/Integration/FlaskIntegrationTest.php
```

### Manual Testing
```bash
# Test via curl
curl -X POST http://localhost:5000/predict \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-api-key" \
  -d '{
    "nitrogen": 90,
    "phosphorus": 42,
    "potassium": 43,
    "temperature": 22.5,
    "humidity": 82,
    "ph": 6.5,
    "rainfall": 200
  }'
```

## Performance Optimization

1. **Caching**
   - Cache model in Flask to avoid reloading
   - Cache predictions by input hash (optional)

2. **Rate Limiting**
   - Flask: 120 requests per minute (configurable)
   - Laravel: Uses Laravel's rate limiting

3. **Database Indexing**
   - PredictionLog has indexes on frequently queried columns
   - Creates separate index for user_id, predicted_crop, status

## Security Considerations

1. **API Key Management**
   - Store API keys in environment variables only
   - Never commit `.env` files to git
   - Rotate keys regularly in production

2. **CORS Configuration**
   - Flask only accepts requests from allowed origins
   - Add production URLs when deploying

3. **Input Validation**
   - Both Flask and Laravel validate inputs
   - Flask checks feature ranges before prediction

4. **Rate Limiting**
   - Prevents abuse of prediction endpoint
   - Configurable via `RATELIMIT_DEFAULT` in Flask

## Deployment Checklist

- [ ] Generate strong API keys for production
- [ ] Update ALLOWED_ORIGINS to production domains
- [ ] Set Flask `FLASK_ENV=production`
- [ ] Set Laravel `APP_DEBUG=false` and `APP_ENV=production`
- [ ] Run database migrations
- [ ] Test all endpoints in production
- [ ] Set up monitoring and alerting
- [ ] Configure backup strategies for both databases
- [ ] Set up proper logging to files/services
- [ ] Test error handling and recovery

## Troubleshooting

### Flask not responding
```bash
# Check if Flask is running
netstat -ano | findstr :5000

# If not, start it:
cd crop_prediction_api
python run.py
```

### API Key issues
```bash
# Verify keys match
# Laravel:
grep CROP_PREDICTION_API_KEY Admin\ Panel/.env

# Flask:
grep API_KEY crop_prediction_api/.env
```

### Database issues
```bash
# Check Laravel database connection
php artisan tinker
> DB::connection()->getPDO()

# Check Flask database
ls -la crop_prediction_api/instance/
```

### Model loading issues
```bash
# Check model file exists
ls -la crop_prediction_api/model/

# Test model loading in Flask shell
python
> from app import create_app
> app = create_app()
> app.extensions['model_service'].status()
```

## Additional Resources

- [Laravel HTTP Client Documentation](https://laravel.com/docs/10.x/http-client)
- [Flask Documentation](https://flask.palletsprojects.com/)
- [Crop Prediction Model Details](../AIModules/README.md)

---

**Last Updated:** April 21, 2024
**Maintained By:** Development Team
