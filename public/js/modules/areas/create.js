// In the following example, markers appear when the user clicks on the map.
// The markers are stored in an array.
// The user can then click an option to hide, show or delete the markers.
let map;
let markers = [];

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(initMap);
    } else { 
        initMap(null);
    }
}

function initMap(position) {
    let haightAshbury = { lat: 37.769, lng: -122.446 };

    if (position != null) {
        haightAshbury = { lat: position.coords.latitude, lng: position.coords.longitude };
    }

    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 12,
        center: haightAshbury,
        mapTypeId: "terrain",
    });

    // This event listener will call addMarker() when the map is clicked.
    map.addListener("click", (event) => {
        addMarker(event.latLng);
    });

    // Adds a marker at the center of the map.
    //addMarker(haightAshbury);

    // Create the search box and link it to the UI element.
    const input = document.getElementById("pac-input");
    const searchBox = new google.maps.places.SearchBox(input);

    //map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
    // Bias the SearchBox results towards current map's viewport.
    map.addListener("bounds_changed", () => {
        searchBox.setBounds(map.getBounds());
    });

    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener("places_changed", () => {
        const places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        // For each place, get the icon, name and location.
        const bounds = new google.maps.LatLngBounds();

        places.forEach((place) => {
            if (!place.geometry || !place.geometry.location) {
                console.log("Returned place contains no geometry");
                return;
            }

            addMarker(place.geometry.location);

            console.log(place.geometry.viewport);
            console.log(place.geometry.location);

            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });

        map.fitBounds(bounds);
    });
}

// Adds a marker to the map and push to the array.
function addMarker(position) {
    deleteMarkers();

    const marker = new google.maps.Marker({
        position,
        map,
    });

    var markerJson = JSON.stringify(position);

    $("#marker").val(markerJson);

    markers.push(marker);
}

// Sets the map on all markers in the array.
function setMapOnAll(map) {
  for (let i = 0; i < markers.length; i++) {
    markers[i].setMap(map);
  }
}

// Removes the markers from the map, but keeps them in the array.
function hideMarkers() {
  setMapOnAll(null);
}

// Deletes all markers in the array by removing references to them.
function deleteMarkers() {
    hideMarkers();
    markers = [];
}

$(function() {
    console.log('entra areas');

    'use strict';
    // Select2
    $('.select2').select2({
        minimumResultsForSearch: Infinity
    });

    var firstLoad = true;

    window.addEventListener('load', function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);

    function showPreview() {
        const file = $("#icon")[0].files[0];

        if (file) {
            $("#imgPreview").attr("src", URL.createObjectURL(file));
        }
    }

    function showPreviewMapa() {
        const file = $("#mapa")[0].files[0];

        if (file) {
            $("#imgPreviewMapa").attr("src", URL.createObjectURL(file));
        }
    }

    $("#city_id").change(() => {
        if (!firstLoad) {
            $.get('/zones/'+$("#city_id").val()+'/city')
                .done(function(data) {
                    var options = "";
                    $.each(data.data, function(index, value){
                        options += "<option value='" + value.id + "'>" + value.name + "</option>";
                    });

                    $('#zone_id')
                        .empty()
                        .append(options);
                })
                .fail(function(xhr) {
                    console.log(xhr);
                });
        } else {
            firstLoad = false;
        }
    });

    $("#icon").change(showPreview);
    $("#mapa").change(showPreviewMapa);
});
