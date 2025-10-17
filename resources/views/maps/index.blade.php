@extends('layouts.app')

@section('title', 'Add Dog-Friendly Location')

@section('content')

    {{-- Page Title --}}
    <h2 class="mb-4">Add a Dog-Friendly Location 🐶📍</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Location Details and Map Selection</h6>
        </div>
        <div class="card-body">

            <div class="row">
                {{-- Left Column: Form Fields --}}
                <div class="col-lg-5 col-md-6 order-md-1 order-2">

                    {{-- Address Search Input --}}
                    <div class="mb-4">
                        <label for="address-search" class="form-label font-weight-bold">Address Search (Auto-Marker)</label>
                        <input type="text" class="form-control" id="address-search" placeholder="E.g., Shoreditch, London...">
                        <small class="form-text text-muted">Search for an address or click and drag a point on the map.</small>
                    </div>

                    {{-- Location Form --}}
                    <form id="locationForm" action="{{ route('locations.store') }}" method="POST">
                        @csrf

                        {{-- Location Title --}}
                        <div class="mb-3">
                            <label for="title" class="form-label">Location Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" required value="{{ old('title') }}">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Latitude & Longitude (Filled by Map) --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" required readonly value="{{ old('latitude') }}">
                                @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="longitude" class="form-label">Longitude <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" required readonly value="{{ old('longitude') }}">
                                @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Address (Filled by Map) --}}
                        <div class="mb-3">
                            <label for="address" class="form-label">Estimated Address</label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="Address will be filled automatically" readonly value="{{ old('address') }}">
                            <small class="form-text text-muted">A reverse-geocoded address of the coordinates.</small>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg mt-3">Save Location</button>
                    </form>
                </div>

                {{-- Right Column: Map --}}
                <div class="col-lg-7 col-md-6 order-md-2 order-1 mb-4">
                    <div id="map" style="height: 550px; width: 100%; border: 1px solid #ccc;"></div>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let map;
    let marker; // Marker for adding new locations
    let geocoder;
    let autocomplete;

    // Controller data sent to the view
    const savedLocations = @json($locations);
    const csrfToken = '{{ csrf_token() }}';

    function initMap() {
        geocoder = new google.maps.Geocoder();
        const initialLocation = { lat: 51.5074, lng: -0.1278 }; // London, UK coordinates

        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: initialLocation,
        });

        setupAddingFeatures();
        loadSavedLocations(savedLocations);
    }

    function setupAddingFeatures() {
        const input = document.getElementById("address-search");
        autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo("bounds", map);

        autocomplete.addListener("place_changed", () => {
            const place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) {
                return;
            }
            map.setCenter(place.geometry.location);
            map.setZoom(17);
            placeMarkerAndPanTo(place.geometry.location, map);
            document.getElementById("address").value = place.formatted_address;
        });

        map.addListener("click", (e) => {
            placeMarkerAndPanTo(e.latLng, map);
            geocodeLatLng(e.latLng);
        });
    }

    function placeMarkerAndPanTo(latLng, map) {
        if (marker) {
            marker.setMap(null);
        }
        marker = new google.maps.Marker({
            position: latLng,
            map: map,
            draggable: true
        });

        marker.addListener('dragend', (e) => {
            placeMarkerAndPanTo(e.latLng, map);
            geocodeLatLng(e.latLng);
        });

        map.panTo(latLng);
        document.getElementById("latitude").value = latLng.lat().toFixed(8);
        document.getElementById("longitude").value = latLng.lng().toFixed(8);
    }

    function geocodeLatLng(latLng) {
        geocoder.geocode({ location: latLng }, (results, status) => {
            if (status === "OK" && results[0]) {
                document.getElementById("address").value = results[0].formatted_address;
            } else {
                document.getElementById("address").value = "";
                console.error("Geocoding failed: " + status);
            }
        });
    }

    // This function loads all saved locations and automatically shows their info windows
    function loadSavedLocations(locations) {
        locations.forEach(location => {
            const latLng = { lat: parseFloat(location.latitude), lng: parseFloat(location.longitude) };

            const savedMarker = new google.maps.Marker({
                position: latLng,
                map: map,
                title: location.title
            });

            // GÜNCELLENMİŞ KİBAR VE KÜÇÜK BAŞLIKLI KUTU İÇERİĞİ (ÜST BOŞLUK AZALTILDI)
            const contentString =
                `<div style="max-width: 200px; padding: 0px 5px 5px 5px;">
                    <h6 style="font-weight: 700; color: #38761D; margin-top: 0px; margin-bottom: 5px; font-size: 0.95em;">📍 ${location.title}</h6>
                    <div style="font-size: 0.85em;">
                        <p style="margin-bottom: 8px;">🏠 ${location.address || 'Address not available'}</p>
                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(${location.id}, '${location.title}')" style="font-size: 0.75em;">
                            🗑️ Delete Location
                        </button>
                    </div>
                </div>`;

            // Create a new InfoWindow for each marker
            const infoWindow = new google.maps.InfoWindow({
                content: contentString,
                // pixelOffset: new google.maps.Size(0, -10) // Opsiyonel: InfoWindow'u yukarı kaydırır
            });

            // Automatically open the info window
            infoWindow.open(map, savedMarker);

            // Tıklayınca yeniden açma özelliği (kullanıcı kapatırsa)
            savedMarker.addListener("click", () => {
                infoWindow.open(map, savedMarker);
            });
        });
    }

    function confirmDelete(locationId, locationTitle) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete the location: ${locationTitle}. This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/locations/${locationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Deleted!',
                            data.success,
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error!', 'An error occurred during deletion.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'Network error or server problem.', 'error');
                });
            }
        })
    }
</script>

<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA5pbr8et6DC72B7sdz2CIKei1my3rh6zo&libraries=places&callback=initMap&language=en">
</script>

@endsection
