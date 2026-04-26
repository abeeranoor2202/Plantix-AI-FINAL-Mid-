# Importing essential libraries and modules

from flask import Flask, render_template, request, jsonify
from markupsafe import Markup
from datetime import datetime, timezone
import numpy as np
import pandas as pd
from utils.disease import disease_dic
from utils.fertilizer import fertilizer_dic
from utils.plant_filter import is_plant_image
import requests
import config
import pickle
import io
import torch
import torch.nn.functional as F
from torchvision import transforms
from PIL import Image
from utils.model import ResNet9
# ==============================================================================================

# -------------------------LOADING THE TRAINED MODELS -----------------------------------------------

# Loading plant disease classification model

disease_classes = ['Apple___Apple_scab',
                   'Apple___Black_rot',
                   'Apple___Cedar_apple_rust',
                   'Apple___healthy',
                   'Blueberry___healthy',
                   'Cherry_(including_sour)___Powdery_mildew',
                   'Cherry_(including_sour)___healthy',
                   'Corn_(maize)___Cercospora_leaf_spot Gray_leaf_spot',
                   'Corn_(maize)___Common_rust_',
                   'Corn_(maize)___Northern_Leaf_Blight',
                   'Corn_(maize)___healthy',
                   'Grape___Black_rot',
                   'Grape___Esca_(Black_Measles)',
                   'Grape___Leaf_blight_(Isariopsis_Leaf_Spot)',
                   'Grape___healthy',
                   'Orange___Haunglongbing_(Citrus_greening)',
                   'Peach___Bacterial_spot',
                   'Peach___healthy',
                   'Pepper,_bell___Bacterial_spot',
                   'Pepper,_bell___healthy',
                   'Potato___Early_blight',
                   'Potato___Late_blight',
                   'Potato___healthy',
                   'Raspberry___healthy',
                   'Soybean___healthy',
                   'Squash___Powdery_mildew',
                   'Strawberry___Leaf_scorch',
                   'Strawberry___healthy',
                   'Tomato___Bacterial_spot',
                   'Tomato___Early_blight',
                   'Tomato___Late_blight',
                   'Tomato___Leaf_Mold',
                   'Tomato___Septoria_leaf_spot',
                   'Tomato___Spider_mites Two-spotted_spider_mite',
                   'Tomato___Target_Spot',
                   'Tomato___Tomato_Yellow_Leaf_Curl_Virus',
                   'Tomato___Tomato_mosaic_virus',
                   'Tomato___healthy']

disease_model_path = 'models/plant_disease_model.pth'
disease_model = ResNet9(3, len(disease_classes))
disease_model.load_state_dict(torch.load(
    disease_model_path, map_location=torch.device('cpu')))
disease_model.eval()


# Loading crop recommendation model

crop_recommendation_model_path = 'models/RandomForest.pkl'
crop_recommendation_model = pickle.load(
    open(crop_recommendation_model_path, 'rb'))


# =========================================================================================

# Custom functions for calculations


def weather_fetch(city_name):
    """
    Fetch and returns the temperature and humidity of a city
    :params: city_name
    :return: temperature, humidity
    """
    api_key = config.weather_api_key
    base_url = "http://api.openweathermap.org/data/2.5/weather?"

    complete_url = base_url + "appid=" + api_key + "&q=" + city_name
    response = requests.get(complete_url)
    x = response.json()

    if x["cod"] != "404":
        y = x["main"]

        temperature = round((y["temp"] - 273.15), 2)
        humidity = y["humidity"]
        return temperature, humidity
    else:
        return None


def predict_image(img, model=disease_model):
    """
    Transforms image to tensor and predicts disease label with confidence scores.
    :params: image bytes
    :return: (top_class_label, confidence_float, all_predictions_list)
    """
    transform = transforms.Compose([
        transforms.Resize(256),
        transforms.ToTensor(),
    ])
    image = Image.open(io.BytesIO(img)).convert("RGB")
    img_t = transform(image)
    img_u = torch.unsqueeze(img_t, 0)

    with torch.no_grad():
        yb    = model(img_u)
        probs = F.softmax(yb, dim=1)[0]

    top5_probs, top5_indices = torch.topk(probs, 5)

    top_label      = disease_classes[top5_indices[0].item()]
    top_confidence = top5_probs[0].item()

    all_predictions = []
    for idx, prob in zip(top5_indices.tolist(), top5_probs.tolist()):
        raw_label    = disease_classes[idx]
        display_name = raw_label.replace("___", " — ").replace("_", " ")
        all_predictions.append({
            "disease":      raw_label.lower(),
            "display_name": display_name,
            "confidence":   round(prob, 4),
        })

    return top_label, top_confidence, all_predictions

# ===============================================================================================
# ------------------------------------ FLASK APP -------------------------------------------------


app = Flask(__name__)

@app.context_processor
def inject_now():
    return {'now': datetime.now(timezone.utc)}

# render home page


@ app.route('/')
def home():
    title = 'Plantix AI - Home'
    return render_template('index.html', title=title)

# render crop recommendation form page


@ app.route('/crop-recommend')
def crop_recommend():
    title = 'Plantix AI - Crop Recommendation'
    return render_template('crop.html', title=title)

# render fertilizer recommendation form page


@ app.route('/fertilizer')
def fertilizer_recommendation():
    title = 'Plantix AI - Fertilizer Suggestion'

    return render_template('fertilizer.html', title=title)

# render disease prediction input page




# ===============================================================================================

# RENDER PREDICTION PAGES

# render crop recommendation result page


@ app.route('/crop-predict', methods=['POST'])
def crop_prediction():
    title = 'Plantix AI - Crop Recommendation'

    if request.method == 'POST':
        N = int(request.form['nitrogen'])
        P = int(request.form['phosphorous'])
        K = int(request.form['pottasium'])
        ph = float(request.form['ph'])
        rainfall = float(request.form['rainfall'])

        temperature = float(request.form['temperature'])
        humidity = float(request.form['humidity'])

        data = np.array([[N, P, K, temperature, humidity, ph, rainfall]])
        my_prediction = crop_recommendation_model.predict(data)
        final_prediction = my_prediction[0]

        return render_template('crop-result.html', prediction=final_prediction, title=title)

# render fertilizer recommendation result page


@ app.route('/fertilizer-predict', methods=['POST'])
def fert_recommend():
    title = 'Plantix AI - Fertilizer Suggestion'

    crop_name = str(request.form['cropname'])
    N = int(request.form['nitrogen'])
    P = int(request.form['phosphorous'])
    K = int(request.form['pottasium'])
    # ph = float(request.form['ph'])

    df = pd.read_csv('Data/fertilizer.csv')

    nr = df[df['Crop'] == crop_name]['N'].iloc[0]
    pr = df[df['Crop'] == crop_name]['P'].iloc[0]
    kr = df[df['Crop'] == crop_name]['K'].iloc[0]

    n = nr - N
    p = pr - P
    k = kr - K
    temp = {abs(n): "N", abs(p): "P", abs(k): "K"}
    max_value = temp[max(temp.keys())]
    if max_value == "N":
        if n < 0:
            key = 'NHigh'
        else:
            key = "Nlow"
    elif max_value == "P":
        if p < 0:
            key = 'PHigh'
        else:
            key = "Plow"
    else:
        if k < 0:
            key = 'KHigh'
        else:
            key = "Klow"

    response = Markup(str(fertilizer_dic[key]))

    return render_template('fertilizer-result.html', recommendation=response, title=title)

# render disease prediction result page


@app.route('/disease-predict', methods=['GET', 'POST'])
def disease_prediction():
    title = 'Plantix AI - Disease Detection'

    if request.method == 'POST':
        if 'file' not in request.files:
            return redirect(request.url)
        file = request.files.get('file')
        if not file:
            return render_template('disease.html', title=title)
        try:
            img = file.read()

            prediction, _, _ = predict_image(img)

            prediction = Markup(str(disease_dic[prediction]))
            return render_template('disease-result.html', prediction=prediction, title=title)
        except:
            pass
    return render_template('disease.html', title=title)


# ── REST API endpoint for Laravel backend ──────────────────────────────────────
# POST /disease/predict
# Headers: X-API-Key: <DISEASE_API_KEY>
# Body:    multipart/form-data  { image: <file> }
#
# Response (success):
#   { status: "success", disease: "tomato___late_blight", display_name: "...",
#     confidence: 0.94, predictions: [{disease, display_name, confidence}, ...] }
#
# Response (invalid image — not a plant):
#   { status: "invalid", message: "...", confidence: 0.0, predictions: [] }
#
# Response (error):
#   { status: "error", message: "..." }  HTTP 400/500
# ──────────────────────────────────────────────────────────────────────────────
@app.route('/disease/predict', methods=['POST'])
def disease_predict_api():
    # ── 1. Optional API key auth ──────────────────────────────────────────────
    expected_key = getattr(config, 'disease_api_key', None)
    if expected_key:
        provided_key = request.headers.get('X-API-Key', '')
        if provided_key != expected_key:
            return jsonify({'status': 'error', 'message': 'Unauthorized'}), 401

    # ── 2. Validate uploaded file ─────────────────────────────────────────────
    if 'image' not in request.files:
        return jsonify({'status': 'error', 'message': 'No image file provided. Send as multipart field "image".'}), 400

    file = request.files['image']
    if file.filename == '':
        return jsonify({'status': 'error', 'message': 'Empty filename.'}), 400

    try:
        img_bytes = file.read()
    except Exception as e:
        return jsonify({'status': 'error', 'message': f'Could not read uploaded file: {str(e)}'}), 400

    # ── 3. MobileNetV2 plant pre-check ────────────────────────────────────────
    # Reject non-plant images (chairs, people, cars, etc.) before running the
    # disease model, which is only trained on plant leaves.
    try:
        plant_ok, top_label, top_conf = is_plant_image(img_bytes)
    except Exception as e:
        # If the filter itself crashes, log and skip it (fail open)
        app.logger.warning(f'Plant filter error (skipping): {e}')
        plant_ok = True
        top_label = 'unknown'
        top_conf  = 0.0

    if not plant_ok:
        return jsonify({
            'status':      'invalid',
            'message':     (
                f'This image does not appear to be a plant leaf. '
                f'MobileNet identified it as "{top_label}" ({round(top_conf * 100, 1)}%). '
                f'Please upload a clear photo of a crop leaf or plant part.'
            ),
            'confidence':  round(top_conf, 4),
            'predictions': [],
        })

    # ── 4. Run ResNet9 disease model ──────────────────────────────────────────
    try:
        top_label_disease, top_confidence, all_predictions = predict_image(img_bytes)
    except Exception as e:
        app.logger.error(f'Disease model inference failed: {e}')
        return jsonify({'status': 'error', 'message': f'Inference failed: {str(e)}'}), 500

    display_name = top_label_disease.replace("___", " — ").replace("_", " ")

    return jsonify({
        'status':       'success',
        'disease':      top_label_disease.lower(),
        'display_name': display_name,
        'confidence':   round(top_confidence, 4),
        'predictions':  all_predictions,
    })


# ===============================================================================================
if __name__ == '__main__':
    app.run(debug=True, port=8001, use_reloader=False)
