// ============= MAP FOR CREATION OF GEOFENCE ============= //
let map;
let marker;
let circle;
let autocomplete;
let geocoder;

const defaultLocation = {
    lat: 14.5995,
    lng: 120.9842
}; // Manila

function initMap() {
    // Initialize map
    map = new google.maps.Map(document.getElementById("map"), {
        center: defaultLocation,
        zoom: 15,
    });

    // Initialize marker
    marker = new google.maps.Marker({
        position: defaultLocation,
        map: map,
        draggable: true,
    });

    // Initialize circle
    circle = new google.maps.Circle({
        map: map,
        radius: parseFloat(document.getElementById('geofenceRadius').value) || 500,
        fillColor: '#AA0000',
        strokeColor: '#AA0000',
        fillOpacity: 0.3,
        strokeOpacity: 0.8,
        strokeWeight: 2,
    });
    circle.bindTo('center', marker, 'position');

    // Initialize geocoder
    geocoder = new google.maps.Geocoder();

    // Initialize autocomplete
    const geofenceAddress = document.getElementById("geofenceAddress");
    autocomplete = new google.maps.places.Autocomplete(geofenceAddress, {
        fields: ["geometry", "formatted_address"],
    });
    autocomplete.bindTo("bounds", map);

    // Optional: Restrict to Philippines only
    autocomplete.setComponentRestrictions({
        country: ["ph"],
    });

    // Handle place selection from autocomplete
    autocomplete.addListener("place_changed", function () {
        const place = autocomplete.getPlace();

        if (!place.geometry || !place.geometry.location) {
            alert("No location found for that input.");
            return;
        }

        // Center map and update marker
        map.setCenter(place.geometry.location);
        map.setZoom(15);
        marker.setPosition(place.geometry.location);

        // Update hidden inputs
        updateLatLngInputs(place.geometry.location.lat(), place.geometry.location.lng());
    });

    // Handle marker drag -> update lat/lng + reverse geocode to get address
    google.maps.event.addListener(marker, "dragend", function () {
        const position = marker.getPosition();
        updateLatLngInputs(position.lat(), position.lng());

        geocoder.geocode({
            location: position
        }, function (results, status) {
            if (status === "OK" && results[0]) {
                geofenceAddress.value = results[0].formatted_address;

                // Trigger input event to ensure autocomplete can re-activate
                geofenceAddress.dispatchEvent(new Event("input", {
                    bubbles: true
                }));
            } else {
                alert("Could not find address for this location.");
            }
        });
    });

    // Update circle radius dynamically
    document.getElementById("geofenceRadius").addEventListener("input", function () {
        const newRadius = parseFloat(this.value);
        if (!isNaN(newRadius)) {
            circle.setRadius(newRadius);
        }
    });

    // Set initial lat/lng values
    updateLatLngInputs(marker.getPosition().lat(), marker.getPosition().lng());
    console.log("Google Maps API loaded");
}
function location_filter() {  
    $.ajax({
        url: geofenceLocationFilterUrl,
        type: 'GET', 
        success: function(response) {
            if (response.status === 'success') {
                $('#locationTableBody').html(response.html);
            } else if (response.status === 'error') {
                toastr.error(response.message || 'Something went wrong.');
            }
        },
        error: function(xhr) {
            let message = 'An unexpected error occurred.';
            if (xhr.status === 403) {
                message = 'You are not authorized to perform this action.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            toastr.error(message);
        }
    });
}
function updateLatLngInputs(lat, lng) {
    document.getElementById("latitude").value = lat;
    document.getElementById("longitude").value = lng;
}

document.addEventListener("DOMContentLoaded", () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    const authToken = localStorage.getItem("token");

    // ============= FORM SUBMISSION ============= //

    const form = document.getElementById("geofencingForm");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        try {
            const response = await fetch("/api/settings/geofence/create", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`,
                    "Accept": "application/json",
                },
                body: JSON.stringify(jsonData),
            });

            const data = await response.json();

            if (response.ok) {
                toastr.success("Geofence created successfully.");
                form.reset();

                $('#add_geofence').modal('hide');
            } else {
                toastr.error(data.message || "Failed to create geofence.");
            }
        } catch (error) {
            console.error("Error creating geofence:", error);
            toastr.error("An error occurred.");
        }
    });

    // ============= EDIT MAP AND EDIT FORM ============= //

    $(document).ready(function () {
        let autocomplete;

        function initEditAutocomplete() {
            const input = document.getElementById("editGeofenceAddress");
            autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.addListener("place_changed", function () {
                const place = autocomplete.getPlace();
                if (!place.geometry) return;

                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();

                $('#editLatitude').val(lat);
                $('#editLongitude').val(lng);
                editMap.setCenter({
                    lat,
                    lng
                });
                editMarker.setPosition({
                    lat,
                    lng
                });
            });
        }

        let editMap, editMarker, editCircle;

        function initEditMap(lat, lng, radius) {
            editMap = new google.maps.Map(document.getElementById("editMap"), {
                center: {
                    lat,
                    lng
                },
                zoom: 15,
            });

            editMarker = new google.maps.Marker({
                position: {
                    lat,
                    lng
                },
                map: editMap,
                draggable: true,
            });

            editCircle = new google.maps.Circle({
                map: editMap,
                radius: parseFloat(radius),
                fillColor: '#AA0000',
                strokeColor: '#AA0000',
                fillOpacity: 0.3,
                strokeOpacity: 0.8,
                strokeWeight: 2,
            });
            editCircle.bindTo('center', editMarker, 'position');

            google.maps.event.addListener(editMarker, 'dragend', function () {
                const lat = editMarker.getPosition().lat();
                const lng = editMarker.getPosition().lng();

                $('#editLatitude').val(lat);
                $('#editLongitude').val(lng);

                // Reverse geocode to update address input
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    location: {
                        lat,
                        lng
                    }
                }, function (results, status) {
                    if (status === "OK") {
                        if (results[0]) {
                            $('#editGeofenceAddress').val(results[0].formatted_address);
                        } else {
                            toastr.warning("No address found for this location.");
                        }
                    } else {
                        toastr.error("Geocoder failed due to: " + status);
                    }
                });
            });

            $('#editGeofenceRadius').on('input', function () {
                editCircle.setRadius(parseFloat(this.value));
            });

            $('#edit_geofence').on('shown.bs.modal', function () {
                google.maps.event.trigger(editMap, 'resize');
                editMap.setCenter(editMarker.getPosition());
            });
        }

        $('.btn-edit').on('click', function () {
            const id = $(this).data('id');
            const name = $(this).data('geofence-name');
            const address = $(this).data('geofence-address');
            const radius = $(this).data('geofence-radius');
            const latitude = parseFloat($(this).data('latitude'));
            const longitude = parseFloat($(this).data('longitude'));
            const expiration = $(this).data('expiration-date');
            const branchId = $(this).data('branch-id');
            const status = $(this).data('status');

            $('#editGeofenceId').val(id);
            $('#editGeofenceName').val(name);
            $('#editGeofenceAddress').val(address);
            $('#editGeofenceRadius').val(radius);
            $('#editLatitude').val(latitude);
            $('#editLongitude').val(longitude);
            $('#editExpirationDate').val(expiration);
            $('#editGeofenceBranchId').val(branchId);
            $('#editGeofenceStatus').val(status);

            initEditMap(latitude, longitude, radius);

            if (!autocomplete) initEditAutocomplete();

            $('#edit_geofence').modal('show');
        });

        $('#editGeofencingForm').on('submit', function (e) {
            e.preventDefault();

            const id = $('#editGeofenceId').val();
            const formData = $(this).serialize(); // This is fine for form data without files

            $.ajax({
                url: `/api/settings/geofence/update/${id}`,
                type: 'PUT',
                data: formData, // You can send the serialized form data
                headers: {
                    'X-CSRF-TOKEN': csrfToken, // Ensure CSRF token is included
                    ...(authToken && {
                        'Authorization': `Bearer ${authToken}` // Include auth token if necessary
                    })
                },
                success: function (response) {
                    toastr.success(response.message || 'Geofence updated successfully');
                    $('#edit_geofence').modal('hide');
                    location_filter();  
                },
                error: function (xhr) {
                    const errorMsg = xhr.responseJSON?.message ||
                        'Failed to update geofence.';
                    toastr.error(errorMsg);
                }
            });
        });
    });

    // ============= DELETE GEOFENCE ============= //
    let deleteId = null;

    const deleteButtons = document.querySelectorAll('.btn-delete');
    const geofenceDeleteBtn = document.getElementById('geofenceDeleteBtn');
    const geofenceNamePlaceHolder = document.getElementById('geofenceNamePlaceHolder');

    // Set up the delete buttons to capture data
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            deleteId = this.getAttribute('data-id');

            const geofenceName = this.getAttribute('data-geofence-name');

            if (geofenceNamePlaceHolder) {
                geofenceNamePlaceHolder.textContent = geofenceName;
            }
        });
    });

    // Confirm delete button click event
    geofenceDeleteBtn?.addEventListener('click', function () {
        if (!deleteId) return;

        fetch(`/api/settings/geofence/delete/${deleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`,
            },
        })
            .then(response => {
                if (response.ok) {
                    toastr.success("Geofence deleted successfully.");

                    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('delete_geofence'));
                    deleteModal.hide(); // Hide the modal

                    setTimeout(() => window.location.reload(), 800); // Refresh the page after a short delay
                } else {
                    return response.json().then(data => {
                        toastr.error(data.message || "Error deleting geofence.");
                    });
                }
            })
            .catch(error => {
                console.error(error);
                toastr.error("Server error.");
            });
    });

});
