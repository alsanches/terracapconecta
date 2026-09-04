import { test, expect } from "@playwright/test";

const waitForMap = async (page) => {
    await page.goto("/");
    await page.waitForFunction(
        () => window.Alpine?.$data(document.querySelector("main")).mapReady,
    );
};

test("loads map workers, region labels and supports region selection and return to DF", async ({
    page,
}) => {
    const errors = [];
    page.on("pageerror", (error) => errors.push(error.message));

    await waitForMap(page);

    await expect(page.locator(".maplibregl-canvas")).toBeVisible();
    await expect(page.locator(".region-map-label")).toHaveCount(35);
    await expect(page.locator(".lot-list button")).toHaveCount(10);

    const canvas = page.locator(".maplibregl-canvas");
    const box = await canvas.boundingBox();

    await canvas.click({
        position: { x: box.width * 0.72, y: box.height * 0.3 },
    });

    await page.waitForFunction(() =>
        Boolean(Alpine.$data(document.querySelector("main")).selectedRegion),
    );

    await expect(page.locator(".region-map-label.is-selected")).toHaveCount(1);

    await page.getByRole("button", { name: "Voltar ao DF" }).click();

    await expect(page.locator(".region-map-label.is-selected")).toHaveCount(0);
    await expect(page.locator(".lot-list button")).toHaveCount(10);

    const firstLabel = page.locator(".region-map-label").first();

    const sizeBefore = await firstLabel.evaluate((element) =>
        Number.parseFloat(getComputedStyle(element).fontSize),
    );

    await page.locator(".maplibregl-ctrl-zoom-in").click();
    await page.waitForTimeout(350);

    const sizeAfter = await firstLabel.evaluate((element) =>
        Number.parseFloat(getComputedStyle(element).fontSize),
    );

    expect(sizeAfter).toBeGreaterThan(sizeBefore);
    expect(errors).toEqual([]);
});

test("each suggested search reveals its ranked demo lot", async ({ page }) => {
    await waitForMap(page);

    for (const [label, region] of [
        ["Bar e gastronomia", "Taguatinga"],
        ["Coworking", "Águas Claras"],
        ["Comércio e serviços", "Planaltina"],
    ]) {
        await page
            .locator(".quick-search")
            .getByRole("button", { name: label, exact: true })
            .click();

        await expect(page.locator(".lot-detail .eyebrow")).toHaveText(region);
        await expect(page.locator(".score-card")).toBeVisible();
        await expect(page.locator(".factor")).toHaveCount(5);
        await expect(page.locator(".lot-list button")).toHaveCount(1);
    }
});

test("mobile map loads and search opens the bottom drawer", async ({
    page,
}) => {
    await page.setViewportSize({ width: 390, height: 844 });

    await waitForMap(page);

    await expect(page.locator(".region-map-label")).toHaveCount(35);

    await page
        .locator(".quick-search")
        .getByRole("button", { name: "Coworking", exact: true })
        .click();

    await expect(page.locator(".side-panel")).toHaveClass(/is-open/);
    await expect(page.locator(".lot-detail .eyebrow")).toHaveText(
        "Águas Claras",
    );
});
