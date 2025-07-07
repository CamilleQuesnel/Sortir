document.addEventListener("turbo:load", function () {
    const spotSelectId = window.spotSelectId;
    const spotSelect = document.getElementById(spotSelectId);

    if (!spotSelect) {
        console.warn("Champ spot non trouvé sur cette page, script-hangout.js ignoré.");
        return;
    }

    console.log('BONJOUR JE SUIS LAAAA')
    const spotInfoDiv = document.getElementById("spot-info");
    const spotAddressSpan = document.getElementById("spot-address");
    const spotLatSpan = document.getElementById("spot-lat");
    const spotLngSpan = document.getElementById("spot-lng");
    const spotZipCodeSpan = document.getElementById("spot-zip-code");
    const spotCitySpan = document.getElementById("spot-city");

    const spotsData = window.spotsData || {};

    function updateSpotInfo(selectedId) {
        const data = spotsData[selectedId];
        if (data) {
            spotAddressSpan.textContent = data.address;
            spotLatSpan.textContent = data.lat;
            spotLngSpan.textContent = data.lng;
            spotZipCodeSpan.textContent = data.zipCode;
            spotCitySpan.textContent = data.city;
            spotInfoDiv.style.display = "block";
        } else {
            spotInfoDiv.style.display = "none";
        }
    }

    document.addEventListener("change", function (event) {
        if (event.target && event.target.id === spotSelect.id) {
            updateSpotInfo(event.target.value);
        }
    });

    updateSpotInfo(spotSelect.value);
});
