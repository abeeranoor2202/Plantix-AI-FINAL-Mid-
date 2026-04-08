@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Breadcrumb/Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{!! route('admin.users') !!}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.user_plural')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.user_create')}}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{trans('lang.user_create')}}</h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card-agri" style="padding: 40px;">
                
                <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.8); color: var(--agri-primary); font-weight: 700; border-radius: 12px; z-index: 10;">
                    <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                    {{trans('lang.processing')}}
                </div>

                <div class="error_top" style="display: none; background: var(--agri-error-light); color: var(--agri-error); padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;"></div>

                <form>
                    {{-- Personal Information --}}
                    <div style="margin-bottom: 40px;">
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-user-circle"></i> {{trans('lang.user_details')}}
                        </h4>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.first_name')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-agri user_first_name" id="firstname" onkeypress="return chkAlphabets(event,'error')" placeholder="e.g. John">
                                <div id="error" class="err text-danger" style="font-size: 11px; margin-top: 4px;"></div>
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">{{ trans("lang.user_first_name_help") }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.last_name')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-agri user_last_name" onkeypress="return chkAlphabets(event,'error1')" placeholder="e.g. Doe">
                                <div id="error1" class="err text-danger" style="font-size: 11px; margin-top: 4px;"></div>
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">{{ trans("lang.user_last_name_help") }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.email')}} <span class="text-danger">*</span></label>
                                <input type="email" class="form-agri user_email" placeholder="john.doe@example.com">
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">{{ trans("lang.user_email_help") }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.user_phone')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-agri user_phone" onkeypress="return chkAlphabets2(event,'error2')" placeholder="+1 (555) 000-0000">
                                <div id="error2" class="err text-danger" style="font-size: 11px; margin-top: 4px;"></div>
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">{{ trans("lang.user_phone_help") }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.password')}} <span class="text-danger">*</span></label>
                                <input type="password" class="form-agri user_password" placeholder="••••••••">
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">{{ trans("lang.user_password_help") }}</div>
                            </div>
                        </div>
                    </div>

					{{-- Address Information --}}
					<div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
						<h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
							<i class="fas fa-map-marker-alt"></i> Customer Address
						</h4>
						<div class="row g-4">
							<div class="col-md-4">
								<label class="agri-label">Address Label</label>
								<select class="form-agri address_label" style="height: 46px;">
									<option value="">Select label</option>
									<option value="Home">Home</option>
									<option value="Work">Work</option>
									<option value="Other">Other</option>
								</select>
							</div>

							<div class="col-md-8">
								<label class="agri-label">Address Line 1</label>
								<input type="text" class="form-agri address_line1" placeholder="House / Street / Area">
							</div>

							<div class="col-md-6">
								<label class="agri-label">Address Line 2</label>
								<input type="text" class="form-agri address_line2" placeholder="Apartment, floor, suite (optional)">
							</div>

							<div class="col-md-6">
								<label class="agri-label">City</label>
								<input type="text" class="form-agri city" placeholder="City">
							</div>

							<div class="col-md-4">
								<label class="agri-label">State / Province</label>
								<input type="text" class="form-agri state" placeholder="State or province">
							</div>

							<div class="col-md-4">
								<label class="agri-label">ZIP / Postal Code</label>
								<input type="text" class="form-agri zip" placeholder="ZIP or postal code">
							</div>

							<div class="col-md-4">
								<label class="agri-label">Country</label>
								<input type="text" class="form-agri country" placeholder="Country">
							</div>
						</div>
					</div>

                    {{-- Profile Image --}}
                    <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                         <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px;">{{trans('lang.store_image')}}</h4>
                         <div style="display: flex; align-items: center; gap: 20px;">
                             <div class="user_image" style="width: 80px; height: 80px; border-radius: 12px; background: white; border: 2px dashed var(--agri-border); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                 <i class="fas fa-image" style="color: var(--agri-border); font-size: 24px;"></i>
                             </div>
                             <div style="flex: 1;">
                                 <input type="file" onChange="handleFileSelect(event)" class="form-control" style="font-size: 13px;">
                                 <div id="uploding_image" style="font-size: 11px; color: var(--agri-primary); margin-top: 8px; font-weight: 600;"></div>
                             </div>
                         </div>
                    </div>

                    {{-- Account Status --}}
                    <div style="margin-bottom: 40px; background: #fffbeb; padding: 24px; border-radius: 16px; border: 1px solid #fde68a; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h5 style="font-size: 15px; font-weight: 700; color: #92400e; margin-bottom: 4px;">Account Activation</h5>
                            <p style="font-size: 13px; color: #b45309; margin: 0;">Enable this user to access the platform immediately.</p>
                        </div>
                        <div class="form-check form-switch" style="padding: 0; margin: 0;">
                            <input type="checkbox" class="user_active" id="user_active" style="width: 50px; height: 26px; cursor: pointer; accent-color: var(--agri-primary);" checked>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <button type="button" class="btn-agri btn-agri-primary save-form-btn" style="flex: 2; height: 50px; font-size: 16px;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> {{trans('lang.save')}}
                        </button>
                        <a href="{!! route('admin.users') !!}" class="btn-agri btn-agri-outline" style="flex: 1; height: 50px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px;">
                             {{trans('lang.cancel')}}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--agri-text-heading);
        margin-bottom: 8px;
        display: block;
    }
</style>
@endsection

@section('scripts')
<script>
	var photo = null;
	var photoFile = null;

	$(".save-form-btn").click(function () {
		var userFirstName = $(".user_first_name").val();
		var userLastName = $(".user_last_name").val();
		var email = $(".user_email").val();
		var password = $(".user_password").val();
		var userPhone = $(".user_phone").val();
		var active = $(".user_active").is(":checked");
		var addressLabel = $(".address_label").val();
		var addressLine1 = $(".address_line1").val();
		var addressLine2 = $(".address_line2").val();
		var city = $(".city").val();
		var state = $(".state").val();
		var zip = $(".zip").val();
		var country = $(".country").val();
		var hasAddressInput = addressLabel !== '' || addressLine1 !== '' || addressLine2 !== '' || city !== '' || state !== '' || zip !== '' || country !== '';

		$(".error_top").hide();

		if (userFirstName == '') {
			$(".error_top").show().html("<p>{{trans('lang.user_firstname_error')}}</p>");
			window.scrollTo(0, 0);
		} else if (userLastName == '') {
			$(".error_top").show().html("<p>{{trans('lang.user_lastname_error')}}</p>");
			window.scrollTo(0, 0);
		} else if (email == '') {
			$(".error_top").show().html("<p>{{trans('lang.user_email_error')}}</p>");
			window.scrollTo(0, 0);
		} else if (password == '') {
			$(".error_top").show().html("<p>{{trans('lang.user_password_error')}}</p>");
			window.scrollTo(0, 0);
		} else if (userPhone == '') {
			$(".error_top").show().html("<p>{{trans('lang.user_phone_error')}}</p>");
			window.scrollTo(0, 0);
		} else if (hasAddressInput && addressLine1 == '') {
			$(".error_top").show().html("<p>Address Line 1 is required when adding an address.</p>");
			window.scrollTo(0, 0);
		} else if (hasAddressInput && city == '') {
			$(".error_top").show().html("<p>City is required when adding an address.</p>");
			window.scrollTo(0, 0);
		} else if (hasAddressInput && country == '') {
			$(".error_top").show().html("<p>Country is required when adding an address.</p>");
			window.scrollTo(0, 0);
		} else {
            jQuery("#data-table_processing").show();

			// Prepare form data for file upload
			var formData = new FormData();
			formData.append('first_name', userFirstName);
			formData.append('last_name', userLastName);
			formData.append('email', email);
			formData.append('password', password);
			formData.append('phone_number', userPhone);
			formData.append('is_active', active ? 1 : 0);
			if (hasAddressInput) {
				formData.append('address_label', addressLabel);
				formData.append('address_line1', addressLine1);
				formData.append('address_line2', addressLine2);
				formData.append('city', city);
				formData.append('state', state);
				formData.append('zip', zip);
				formData.append('country', country);
			}
			if (photoFile) {
				formData.append('profile_picture', photoFile);
			}

			// Create user via backend API
			$.ajax({
					url: '{{ route("admin.users.api.store") }}',
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				processData: false,
				contentType: false,
				data: formData,
				success: function(response) {
					jQuery("#data-table_processing").hide();
					window.location.href = '{{ route("admin.users")}}';
				},
				error: function(xhr) {
					jQuery("#data-table_processing").hide();
					var message = 'Error creating user';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						message = xhr.responseJSON.message;
					}
					$(".error_top").show().html("<p>" + message + "</p>");
				}
			});
		}
	})

	function handleFileSelect(evt) {
		var f = evt.target.files[0];
		if (!f) return;

		var reader = new FileReader();
		photoFile = f;

		reader.onload = (function (theFile) {
			return function (e) {
				var filePayload = e.target.result;
				$(".user_image").html('<img class="rounded" style="width:100%; height:100%; object-fit:cover;" src="' + filePayload + '" alt="image">');
				$("#uploding_image").text('File selected: ' + f.name);
			};
		})(f);
		reader.readAsDataURL(f);
	}

    function chkAlphabets(event,msg) {
		if(!(event.which>=97 && event.which<=122) && !(event.which>=65 && event.which<=90) ) {
		    document.getElementById(msg).innerHTML="Letters only please";
		    return false;
		} else {
		    document.getElementById(msg).innerHTML="";
		    return true;
		}
	}

	function chkAlphabets2(event,msg) {
		if(!(event.which>=48  && event.which<=57)) {
		    document.getElementById(msg).innerHTML="Numbers only please";
		    return false;
		} else {
		    document.getElementById(msg).innerHTML="";
		    return true;
		}
	}

	function getCookie(name) {
		const nameEQ = name + "=";
		const cookies = document.cookie.split(';');
		for (let cookie of cookies) {
			cookie = cookie.trim();
			if (cookie.indexOf(nameEQ) === 0) {
				return cookie.substring(nameEQ.length);
			}
		}
		return '';
	}
</script>
@endsection