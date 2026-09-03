import "./bootstrap";
import Alpine from "alpinejs";
import { maplibregl } from "./map-engine";
import "maplibre-gl/dist/maplibre-gl.css";

window.Alpine = Alpine;

Alpine.data("terracapMap", () => {
    let map = null;

    return {
        mapReady: false,
        regions: null,
        lots: [],
        visibleLots: [],
        selectedRegion: null,
        selectedLot: null,
        recommendation: null,
        query: "",
        loading: true,
        searching: false,
        message: "",
        suggestions: [],
        currency: new Intl.NumberFormat("pt-BR", {
            style: "currency",
            currency: "BRL",
        }),

        async init() {
            try {
                const [regionsResponse, lotsResponse] = await Promise.all([
                    fetch("/api/v1/regions"),
                    fetch("/api/v1/lots"),
                ]);
                if (!regionsResponse.ok || !lotsResponse.ok)
                    throw new Error(
                        "Não foi possível carregar os dados do mapa.",
                    );
                this.regions = await regionsResponse.json();
                const lotsPayload = await lotsResponse.json();
                this.lots = lotsPayload.data;
                this.visibleLots = this.lots;
                this.createMap();
            } catch (error) {
                this.message = error.message;
            } finally {
                this.loading = false;
            }
        },

        createMap() {
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
                center: [-47.84, -15.78],
                zoom: 8.35,
                minZoom: 7,
                maxZoom: 15,
                attributionControl: false,
            });
            map.addControl(
                new maplibregl.NavigationControl({ showCompass: false }),
                "bottom-right",
            );
            map.addControl(
                new maplibregl.AttributionControl({
                    compact: true,
                    customAttribution: "Limites: IPEDF",
                }),
                "bottom-left",
            );
            map.on("error", () => {
                this.message =
                    "Não foi possível desenhar o mapa. Recarregue a página para tentar novamente.";
            });
            map.on("idle", () => {
                this.mapReady = Boolean(
                    map.getSource("regions") &&
                    map.isSourceLoaded("regions") &&
                    map.isSourceLoaded("lots"),
                );
            });
            map.on("load", () => {
                map.addSource("regions", {
                    type: "geojson",
                    data: Alpine.raw(this.regions),
                });
                map.addLayer({
                    id: "regions-fill",
                    type: "fill",
                    source: "regions",
                    paint: {
                        "fill-color": [
                            "case",
                            ["boolean", ["feature-state", "selected"], false],
                            "#d6a83f",
                            "#2f725e",
                        ],
                        "fill-opacity": [
                            "case",
                            ["boolean", ["feature-state", "muted"], false],
                            0.12,
                            ["boolean", ["feature-state", "selected"], false],
                            0.78,
                            0.55,
                        ],
                    },
                });
                map.addLayer({
                    id: "regions-line",
                    type: "line",
                    source: "regions",
                    paint: {
                        "line-color": [
                            "case",
                            ["boolean", ["feature-state", "selected"], false],
                            "#78591a",
                            "#f8f3e8",
                        ],
                        "line-width": [
                            "case",
                            ["boolean", ["feature-state", "selected"], false],
                            3,
                            1.1,
                        ],
                    },
                });
                map.addSource("lots", {
                    type: "geojson",
                    data: this.lotsGeoJson(),
                });
                map.addLayer({
                    id: "lot-halo",
                    type: "circle",
                    source: "lots",
                    paint: {
                        "circle-radius": ["case", ["get", "featured"], 13, 10],
                        "circle-color": "#fff8e7",
                        "circle-opacity": 0.9,
                    },
                });
                map.addLayer({
                    id: "lots",
                    type: "circle",
                    source: "lots",
                    paint: {
                        "circle-radius": ["case", ["get", "featured"], 8, 6],
                        "circle-color": [
                            "case",
                            ["get", "featured"],
                            "#d29d2b",
                            "#123f36",
                        ],
                        "circle-stroke-color": "#ffffff",
                        "circle-stroke-width": 1.5,
                    },
                });
                map.on("mouseenter", "regions-fill", () => {
                    map.getCanvas().style.cursor = "pointer";
                });
                map.on("mouseleave", "regions-fill", () => {
                    map.getCanvas().style.cursor = "";
                });
                map.on("click", "regions-fill", (event) => {
                    if (
                        map.queryRenderedFeatures(event.point, {
                            layers: ["lots"],
                        }).length
                    )
                        return;
                    this.selectRegion(event.features[0].properties.slug);
                });
                map.on("click", "lots", (event) => {
                    event.originalEvent.cancelBubble = true;
                    this.openLot(event.features[0].properties.id);
                });
                this.fitAll();
            });
        },

        lotsGeoJson() {
            return {
                type: "FeatureCollection",
                features: this.visibleLots.map((lot) => ({
                    type: "Feature",
                    geometry: {
                        type: "Point",
                        coordinates: [
                            Number(lot.coordinates[0]),
                            Number(lot.coordinates[1]),
                        ],
                    },
                    properties: {
                        id: lot.id,
                        featured: lot.is_featured,
                        region: lot.region.slug,
                    },
                })),
            };
        },

        updateLotsLayer() {
            const source = map?.getSource("lots");
            if (source) source.setData(this.lotsGeoJson());
        },

        selectRegion(slug) {
            const feature = this.regions.features.find(
                (item) => item.properties.slug === slug,
            );
            if (!feature) return;
            this.selectedRegion = feature.properties;
            this.selectedLot = null;
            this.recommendation = null;
            this.message = "";
            this.visibleLots = this.lots.filter(
                (lot) => lot.region.slug === slug,
            );
            this.updateLotsLayer();
            this.setRegionStates(slug);
            this.fitFeature(feature.geometry, 56);
        },

        setRegionStates(selectedSlug = null) {
            this.regions.features.forEach((feature) => {
                map.setFeatureState(
                    { source: "regions", id: feature.id },
                    {
                        selected: feature.properties.slug === selectedSlug,
                        muted:
                            selectedSlug !== null &&
                            feature.properties.slug !== selectedSlug,
                    },
                );
            });
        },

        resetMap() {
            this.selectedRegion = null;
            this.selectedLot = null;
            this.recommendation = null;
            this.message = "";
            this.query = "";
            this.visibleLots = this.lots;
            this.updateLotsLayer();
            this.setRegionStates();
            this.fitAll();
        },

        async search(term = null) {
            if (term) this.query = term;
            if (this.query.trim().length < 2) return;
            this.searching = true;
            this.selectedLot = null;
            this.selectedRegion = null;
            try {
                const response = await fetch(
                    `/api/v1/recommendations?query=${encodeURIComponent(this.query)}`,
                );
                const payload = await response.json();
                if (!response.ok)
                    throw new Error(
                        "Revise o texto informado e tente novamente.",
                    );
                this.recommendation = payload.recognized ? payload : null;
                this.suggestions = payload.suggestions || [];
                this.message = payload.message;
                this.visibleLots = payload.recognized
                    ? this.lots.filter((lot) =>
                          payload.results.some(
                              (result) => result.lot_id === lot.id,
                          ),
                      )
                    : this.lots;
                this.updateLotsLayer();
                this.setRegionStates();
                if (payload.results?.length) {
                    this.openLot(payload.results[0].lot_id, false);
                    map.flyTo({
                        center: payload.results[0].coordinates,
                        zoom: 12.2,
                        essential: true,
                    });
                } else {
                    this.fitAll();
                }
            } catch (error) {
                this.message = error.message;
            } finally {
                this.searching = false;
            }
        },

        openLot(id, clearRecommendation = true) {
            this.selectedLot = this.lots.find(
                (lot) => Number(lot.id) === Number(id),
            );
            if (clearRecommendation) {
                this.recommendation = null;
                this.message = "";
            }
            if (this.selectedLot)
                map.flyTo({
                    center: this.selectedLot.coordinates,
                    zoom: Math.max(map.getZoom(), 11.5),
                    essential: true,
                });
        },

        scoreFor(id) {
            return this.recommendation?.results?.find(
                (result) => Number(result.lot_id) === Number(id),
            );
        },

        fitAll() {
            if (this.regions)
                this.fitFeature(
                    {
                        coordinates: this.regions.features.map(
                            (feature) => feature.geometry.coordinates,
                        ),
                    },
                    28,
                );
        },

        fitFeature(geometry, padding) {
            const bounds = new maplibregl.LngLatBounds();
            const extend = (node) => {
                if (
                    Array.isArray(node) &&
                    node.length >= 2 &&
                    typeof node[0] === "number" &&
                    typeof node[1] === "number"
                )
                    return bounds.extend(node);
                if (Array.isArray(node)) node.forEach(extend);
            };
            extend(geometry.coordinates);
            map.fitBounds(bounds, { padding, duration: 800, maxZoom: 12 });
        },

        formatArea(value) {
            return (
                new Intl.NumberFormat("pt-BR", {
                    maximumFractionDigits: 0,
                }).format(value) + " m²"
            );
        },
    };
});

Alpine.start();
