import { maplibregl } from "./map-engine";

window.terracapLocationPicker = ({
    latitude,
    longitude,
    latitudePath,
    longitudePath,
}) => {
    let map = null;
    let marker = null;

    return {
        async mount() {
            const response = await fetch("/api/v1/regions");
            const regions = await response.json();
            map = new maplibregl.Map({
                container: this.$refs.map,
                style: {
                    version: 8,
                    sources: {},
                    layers: [
                        {
                            id: "background",
                            type: "background",
                            paint: { "background-color": "#edf1e8" },
                        },
                    ],
                },
                center:
                    longitude && latitude
                        ? [Number(longitude), Number(latitude)]
                        : [-47.84, -15.78],
                zoom: longitude && latitude ? 12 : 8.2,
                attributionControl: false,
            });
            map.addControl(
                new maplibregl.NavigationControl({ showCompass: false }),
                "top-right",
            );
            map.on("load", () => {
                map.addSource("admin-regions", {
                    type: "geojson",
                    data: regions,
                });
                map.addLayer({
                    id: "admin-regions-fill",
                    type: "fill",
                    source: "admin-regions",
                    paint: { "fill-color": "#2f725e", "fill-opacity": 0.42 },
                });
                map.addLayer({
                    id: "admin-regions-line",
                    type: "line",
                    source: "admin-regions",
                    paint: { "line-color": "#f8f3e8", "line-width": 1 },
                });
                if (longitude && latitude)
                    this.placeMarker(Number(longitude), Number(latitude));
            });
            map.on("click", (event) => {
                const lng = Number(event.lngLat.lng.toFixed(7));
                const lat = Number(event.lngLat.lat.toFixed(7));
                this.placeMarker(lng, lat);
                this.$wire.set(latitudePath, lat);
                this.$wire.set(longitudePath, lng);
            });
        },

        placeMarker(longitudeValue, latitudeValue) {
            if (marker) marker.remove();
            marker = new maplibregl.Marker({ color: "#d6a83f" })
                .setLngLat([longitudeValue, latitudeValue])
                .addTo(map);
        },
    };
};
