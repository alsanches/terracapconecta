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
    await expect(page.locator(".lot-list button")).toHaveCount(12);

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
    await expect(page.locator(".lot-list button")).toHaveCount(12);

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

test("religious search is a historical catalog without score", async ({ page }) => {
    await waitForMap(page);
    await page.locator(".quick-search").getByRole("button", {
        name: "Templos e assistência social",
        exact: true,
    }).click();

    await expect(page.locator(".lot-list button")).toHaveCount(2);
    await expect(page.locator(".official-tag")).toHaveText("Referência pública real");
    await expect(page.locator(".score-card")).toHaveCount(0);
    await expect(page.locator(".catalog-note")).toContainText("sem disponibilidade atual");
});

test("public notice details open accessibly and return focus", async ({ page }) => {
    await waitForMap(page);
    await page.locator("#editais").scrollIntoViewIfNeeded();
    const firstButton = page.locator(".notices-table .detail-link").first();

    await expect(page.locator(".notices-table tbody tr")).toHaveCount(4);
    await expect(page.locator(".notices-link span")).toHaveText("2");
    await firstButton.click();
    await expect(page.locator('[x-ref="noticeDialog"]')).toHaveJSProperty("open", true);
    await expect(page.locator('[x-ref="noticeClose"]')).toBeFocused();
    await page.keyboard.press("Escape");
    await expect(firstButton).toBeFocused();
});

test("demo request stays in the browser and clears when closed", async ({ page }) => {
    const nonGetRequests = [];
    page.on("request", (request) => {
        if (request.method() !== "GET") nonGetRequests.push(request.method() + " " + request.url());
    });

    await waitForMap(page);
    await page.locator(".lot-list button").first().click();
    const trigger = page.getByRole("button", { name: "Simular requerimento" });
    await trigger.click();

    await expect(page.getByRole("heading", { name: "Simular requerimento" })).toBeVisible();
    await page.getByLabel("Nome").fill("Pessoa Fictícia");
    await page.getByLabel("CPF/CNPJ").fill("DOCUMENTO-FICTICIO");
    await page.getByLabel("Telefone").fill("TELEFONE-FICTICIO");
    await page.getByLabel("E-mail").fill("teste@exemplo.invalid");
    await page.getByLabel("Endereço para correspondência").fill("Endereço fictício");
    await page.getByLabel("CEP").fill("CEP-FICTICIO");
    await page.getByLabel("Texto do requerimento").fill("Solicitação exclusivamente demonstrativa.");
    await page.getByLabel(/Estou ciente/).check();
    await page.getByRole("button", { name: "Simular envio" }).click();

    await expect(page.locator(".request-receipt strong")).toHaveText(/^SIM-/);
    expect(nonGetRequests).toEqual([]);
    await page.keyboard.press("Escape");
    await expect(trigger).toBeFocused();
    await trigger.click();
    await expect(page.getByLabel("Nome")).toHaveValue("");
    await page.keyboard.press("Escape");
});

test("notices become cards on mobile", async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await waitForMap(page);
    await page.locator("#editais").scrollIntoViewIfNeeded();

    await expect(page.locator(".notices-table thead")).toBeHidden();
    await expect(page.locator(".notices-table tbody tr").first()).toBeVisible();
});
