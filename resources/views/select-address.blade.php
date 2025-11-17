<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn địa chỉ</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body { height: 100%; font-family: Arial, sans-serif; overflow: hidden; }

        #map { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; }

        .address-container { position: absolute; top: 15px; left: 50%; transform: translateX(-50%); background-color: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); z-index: 1000; max-width: 450px; width: 90%; }

        .header { text-align: center; margin-bottom: 12px; }

        .header h1 { font-size: 18px; font-weight: 600; color: #333; }

        .form-group { margin-bottom: 10px; }

        .form-label { display: block; font-size: 12px; font-weight: 600; color: #333; margin-bottom: 5px; }

        .input-wrapper { position: relative; display: flex; align-items: center; }

        .input-wrapper input { width: 100%; padding: 10px 35px 10px 35px; border: 1px solid #ddd; border-radius: 25px; font-size: 13px; outline: none; transition: all 0.3s; background-color: white; }

        .input-wrapper input:focus { border-color: #004d2e; box-shadow: 0 0 0 3px rgba(0, 77, 46, 0.1); }

        .input-icon { position: absolute; left: 12px; font-size: 15px; pointer-events: none; }

        .clear-btn { position: absolute; right: 12px; background: none; border: none; color: #999; font-size: 16px; cursor: pointer; padding: 4px; display: none; line-height: 1; }

        .input-wrapper input:not(:placeholder-shown) ~ .clear-btn { display: block; }

        .clear-btn:hover { color: #333; }

        .optional-label { color: #999; font-weight: 400; font-size: 11px; }

        .button-group { display: flex; gap: 10px; margin-top: 8px; }

        .location-btn { background: white; color: #004d2e; border: 1px solid #004d2e; border-radius: 20px; padding: 8px 14px; font-size: 12px; cursor: pointer; font-weight: 600; transition: all 0.3s; flex: 0 0 auto; white-space: nowrap; }

        .location-btn:hover { background: #f0f8f5; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 77, 46, 0.2); }

        .location-btn:active { transform: translateY(0); }

        .continue-btn { background: #004d2e; color: white; border: none; border-radius: 20px; padding: 8px 16px; font-size: 13px; cursor: pointer; font-weight: 600; transition: all 0.3s; flex: 1; }

        .continue-btn:hover { background: #003d24; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 77, 46, 0.3); }

        .continue-btn:active { transform: translateY(0); }

        .pac-container { border-radius: 8px; margin-top: 5px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15); border: none; font-family: Arial, sans-serif; z-index: 10000; }

        @media (max-width: 768px) {
            .address-container { padding: 15px; width: 95%; top: 10px; }
            .header h1 { font-size: 16px; }
            .input-wrapper input { padding: 9px 32px 9px 32px; font-size: 12px; }
            .form-label { font-size: 11px; }
            .button-group { flex-direction: column; }
            .location-btn { flex: 1; }
        }

        @media (max-width: 480px) {
            .address-container { padding: 12px; top: 8px; }
            .header h1 { font-size: 15px; margin-bottom: 8px; }
            .form-group { margin-bottom: 8px; }
        }
    </style>
</head>
<body>
    <div id="map"></div>

    <div class="address-container">
        <div class="header">
            <h1>Vui lòng chọn địa chỉ của bạn</h1>
        </div>

        <div class="form-group">
            <label class="form-label">Địa chỉ</label>
            <div class="input-wrapper">
                <span class="input-icon">📍</span>
                <input type="text" id="street-address" placeholder="VD. 140 Lê Trọng Tấn, phường Tây Thạnh, quận Tân Phú, TPHCM" autocomplete="off">
                <button class="clear-btn" onclick="clearInput('street-address')">✕</button>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Số tòa nhà/ Căn hộ <span class="optional-label">(Tùy chọn)</span></label>
            <div class="input-wrapper">
                <span class="input-icon">🏢</span>
                <input type="text" id="unit-address" placeholder="VD. Căn hộ số 30/ Tòa nhà số 7" autocomplete="off">
                <button class="clear-btn" onclick="clearInput('unit-address')">✕</button>
            </div>
        </div>

        <div class="button-group">
            <button class="location-btn" onclick="useMyLocation()">📍 Vị trí hiện tại</button>
            <button class="continue-btn" onclick="continueToNextPage()">Tiếp tục →</button>
        </div>
    </div>

    <script>
        let map;
        let marker;
        let geocoder;
        let autocomplete;

        function initMap() {
            const defaultLocation = { lat: 10.8231, lng: 106.6297 };
            
            map = new google.maps.Map(document.getElementById('map'), {
                center: defaultLocation,
                zoom: 13,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
                zoomControl: true,
                zoomControlOptions: { position: google.maps.ControlPosition.RIGHT_CENTER }
            });

            geocoder = new google.maps.Geocoder();

            marker = new google.maps.Marker({ map, draggable: true, position: defaultLocation, animation: google.maps.Animation.DROP });

            autocomplete = new google.maps.places.Autocomplete(
                document.getElementById('street-address'),
                { types: ['address'] }
            );

            autocomplete.bindTo('bounds', map);

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place.geometry) return;

                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }

                marker.setPosition(place.geometry.location);
                marker.setAnimation(google.maps.Animation.BOUNCE);
                setTimeout(() => marker.setAnimation(null), 750);
            });

            map.addListener('click', function(event) {
                marker.setPosition(event.latLng);
                marker.setAnimation(google.maps.Animation.BOUNCE);
                setTimeout(() => marker.setAnimation(null), 750);
                geocodeLatLng(event.latLng);
            });

            marker.addListener('dragend', function(event) {
                geocodeLatLng(event.latLng);
            });

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLocation = { lat: position.coords.latitude, lng: position.coords.longitude };
                        map.setCenter(userLocation);
                        marker.setPosition(userLocation);
                        geocodeLatLng(userLocation);
                    },
                    function(error) {
                        console.log('Không thể lấy vị trí:', error);
                    }
                );
            }
        }

        function geocodeLatLng(latLng) {
            geocoder.geocode({ location: latLng }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    document.getElementById('street-address').value = results[0].formatted_address;
                }
            });
        }

        function useMyLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLocation = { lat: position.coords.latitude, lng: position.coords.longitude };
                        map.setCenter(userLocation);
                        map.setZoom(17);
                        marker.setPosition(userLocation);
                        marker.setAnimation(google.maps.Animation.BOUNCE);
                        setTimeout(() => marker.setAnimation(null), 750);
                        geocodeLatLng(userLocation);
                    },
                    function(error) {
                        alert('Không thể lấy vị trí của bạn. Vui lòng cho phép truy cập vị trí trong cài đặt trình duyệt.');
                    }
                );
            } else {
                alert('Trình duyệt của bạn không hỗ trợ định vị.');
            }
        }

        function clearInput(inputId) {
            document.getElementById(inputId).value = '';
            if (inputId === 'street-address') {
                document.getElementById(inputId).focus();
            }
        }

        function continueToNextPage() {
            const streetAddress = document.getElementById('street-address').value;
            const unitAddress = document.getElementById('unit-address').value;
            
            if (!streetAddress.trim()) {
                alert('Vui lòng nhập địa chỉ của bạn');
                return;
            }

            localStorage.setItem('streetAddress', streetAddress);
            localStorage.setItem('unitAddress', unitAddress);
            
            window.location.href = "{{ url('booking') }}";
        }
    </script>
    
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBu4n17b1QXeenFSYC07lzTKet5siXlnuU&libraries=places&callback=initMap" async defer></script>
</body>
</html>
