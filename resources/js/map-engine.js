import * as maplibregl from "maplibre-gl";
import workerUrl from "maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url";

// MapLibre 6 ships a separate worker; let Vite bundle it with its dependencies.
maplibregl.setWorkerUrl(workerUrl);

export { maplibregl };
