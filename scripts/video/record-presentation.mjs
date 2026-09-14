import { chromium } from "playwright";
import fs from "node:fs/promises";
import path from "node:path";

const outputDirectory = path.resolve(process.argv[2] ?? "artifacts/terracap-video");
const productionUrl = "https://terracap-conecta.179-198-101-134.sslip.io/";
const timeline = JSON.parse(
  await fs.readFile(path.join(outputDirectory, "timeline.json"), "utf8"),
);

const sceneById = new Map(timeline.scenes.map((scene) => [scene.id, scene]));
const delay = (milliseconds) => new Promise((resolve) => setTimeout(resolve, milliseconds));

function slideHtml({ kicker, title, body, items = [], closing = false }) {
  const itemMarkup = items
    .map((item) => `<li>${item}</li>`)
    .join("");

  return `<!doctype html>
  <html lang="pt-BR">
  <head>
    <meta charset="utf-8">
    <style>
      * { box-sizing: border-box; }
      html, body { width: 100%; height: 100%; margin: 0; }
      body {
        overflow: hidden;
        background:
          radial-gradient(circle at 82% 20%, rgba(208, 164, 46, .2), transparent 26%),
          linear-gradient(135deg, #f7f5ed 0%, #edf4ef 100%);
        color: #123f35;
        font-family: Arial, Helvetica, sans-serif;
      }
      .frame { height: 100%; display: grid; grid-template-columns: 1.18fr .82fr; }
      .content { padding: 120px 120px 90px 150px; display: flex; flex-direction: column; justify-content: center; }
      .brand { display: flex; align-items: center; gap: 20px; position: absolute; left: 150px; top: 68px; }
      .mark { width: 66px; height: 66px; border-radius: 12px; display: grid; place-items: center; color: white; background: #006f45; border-top: 7px solid #d6a83f; font: 700 25px Georgia, serif; }
      .brand-text strong { display: block; font: 700 28px Georgia, serif; }
      .brand-text span { display: block; margin-top: 6px; letter-spacing: .35em; font-size: 13px; }
      .kicker { color: #087d50; font-weight: 800; letter-spacing: .22em; font-size: 17px; text-transform: uppercase; margin-bottom: 24px; }
      h1 { margin: 0; max-width: 1080px; font: italic 700 ${closing ? 92 : 76}px/1.03 Georgia, serif; letter-spacing: -.035em; }
      p { max-width: 1050px; font-size: 31px; line-height: 1.45; color: #3c5d55; margin: 32px 0 0; }
      ul { list-style: none; padding: 0; margin: 38px 0 0; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; max-width: 1050px; }
      li { background: rgba(255,255,255,.82); border: 1px solid rgba(18,63,53,.15); border-radius: 16px; padding: 22px 26px; font-size: 25px; font-weight: 700; box-shadow: 0 12px 35px rgba(18,63,53,.06); }
      .visual { position: relative; overflow: hidden; background: #123f35; }
      .visual::before, .visual::after { content: ''; position: absolute; border-radius: 50%; border: 70px solid rgba(255,255,255,.08); }
      .visual::before { width: 710px; height: 710px; left: -170px; bottom: -240px; }
      .visual::after { width: 390px; height: 390px; right: -130px; top: -100px; border-color: rgba(214,168,63,.25); }
      .visual-label { position: absolute; inset: 0; display: grid; place-items: center; color: #fff; padding: 80px; text-align: center; font: 700 44px/1.22 Georgia, serif; }
      .footer { position: absolute; left: 150px; bottom: 50px; color: #58736c; font-size: 16px; letter-spacing: .08em; }
    </style>
  </head>
  <body>
    <div class="brand"><div class="mark">TC</div><div class="brand-text"><strong>Terracap</strong><span>CONECTA</span></div></div>
    <div class="frame">
      <main class="content">
        <div class="kicker">${kicker}</div>
        <h1>${title}</h1>
        ${body ? `<p>${body}</p>` : ""}
        ${items.length ? `<ul>${itemMarkup}</ul>` : ""}
      </main>
      <aside class="visual"><div class="visual-label">Território<br>+<br>oportunidade<br>+<br>desenvolvimento</div></aside>
    </div>
    <div class="footer">PROTÓTIPO DEMONSTRATIVO • DISTRITO FEDERAL</div>
  </body>
  </html>`;
}

async function runScene(page, id, action) {
  const scene = sceneById.get(id);
  if (!scene) throw new Error(`Cena ${id} não encontrada na linha do tempo.`);

  const startedAt = Date.now();
  await action(scene.duration_ms);
  const remaining = scene.duration_ms - (Date.now() - startedAt);
  if (remaining > 0) await delay(remaining);
}

async function waitForMap(page) {
  await page.waitForFunction(
    () => window.Alpine?.$data(document.querySelector("main"))?.mapReady,
    { timeout: 30000 },
  );
}

await fs.mkdir(outputDirectory, { recursive: true });

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({
  viewport: { width: 1920, height: 1080 },
  deviceScaleFactor: 1,
  recordVideo: {
    dir: path.join(outputDirectory, "raw"),
    size: { width: 1920, height: 1080 },
  },
});

const page = await context.newPage();
const video = page.video();
const pageErrors = [];
page.on("pageerror", (error) => pageErrors.push(error.message));

await page.setContent(
  slideHtml({
    kicker: "Inovação territorial",
    title: "O lugar certo para uma boa ideia.",
    body: "Uma nova experiência para conectar imóveis, editais públicos e inteligência territorial.",
    closing: true,
  }),
);

await delay(timeline.intro_ms);

await runScene(page, "01", async () => {});

await runScene(page, "02", async () => {
  await page.goto(productionUrl, { waitUntil: "domcontentloaded", timeout: 30000 });
  await waitForMap(page);
  await page.locator("body").evaluate((element) => element.scrollTo?.(0, 0)).catch(() => {});
});

await runScene(page, "03", async (duration) => {
  await page.locator(".workspace").scrollIntoViewIfNeeded();
  await page.evaluate(() => {
    window.Alpine.$data(document.querySelector("main")).selectRegion("taguatinga");
  });
  await delay(Math.min(2200, Math.floor(duration * 0.18)));
  await page.evaluate(() => {
    const state = window.Alpine.$data(document.querySelector("main"));
    const lot = state.lots.find((item) => item.region.slug === "taguatinga");
    if (!lot) throw new Error("Lote de Taguatinga não encontrado.");
    state.openLot(lot.id);
  });
  await page.locator(".workspace").scrollIntoViewIfNeeded();
});

await runScene(page, "04", async () => {
  await page.locator(".quick-search").getByRole("button", { name: "Coworking", exact: true }).click();
  await page.locator(".workspace").scrollIntoViewIfNeeded();
  await page.waitForSelector(".score-card");
});

await runScene(page, "05", async () => {
  await page.locator(".quick-search").getByRole("button", { name: "Templos e assistência social", exact: true }).click();
  await page.locator(".workspace").scrollIntoViewIfNeeded();
  await page.waitForSelector(".catalog-note");
});

await runScene(page, "06", async () => {
  await page.locator("#editais").scrollIntoViewIfNeeded();
  await page.locator(".notices-table .detail-link").first().click();
  await page.waitForSelector('[x-ref="noticeDialog"][open]');
});

await runScene(page, "07", async (duration) => {
  await page.keyboard.press("Escape");
  await page.locator(".quick-search").getByRole("button", { name: "Bar e gastronomia", exact: true }).click();
  await page.locator(".workspace").scrollIntoViewIfNeeded();
  await page.getByRole("button", { name: "Simular requerimento", exact: true }).click();
  await delay(Math.min(6500, Math.floor(duration * 0.3)));

  await page.getByLabel("Nome", { exact: true }).fill("Pessoa Fictícia");
  await page.getByLabel("CPF/CNPJ", { exact: true }).fill("DOCUMENTO-FICTÍCIO");
  await page.getByLabel("Telefone", { exact: true }).fill("TELEFONE-FICTÍCIO");
  await page.getByLabel("E-mail", { exact: true }).fill("teste@exemplo.invalid");
  await page.getByLabel("Endereço para correspondência", { exact: true }).fill("Endereço fictício");
  await page.getByLabel("CEP", { exact: true }).fill("CEP-FICTÍCIO");
  await page.getByLabel("Texto do requerimento", { exact: true }).fill("Solicitação exclusivamente demonstrativa.");
  await page.getByLabel(/Estou ciente/).check();
  await page.getByRole("button", { name: "Simular envio", exact: true }).click();
  await page.waitForSelector(".request-receipt");
});

await runScene(page, "08", async (duration) => {
  await page.goto(`${productionUrl}admin`, { waitUntil: "domcontentloaded", timeout: 30000 });
  await delay(Math.min(5200, Math.floor(duration * 0.32)));
  await page.setContent(
    slideHtml({
      kicker: "Administração e governança",
      title: "Gestão preparada para evoluir.",
      body: "Acesso protegido e dados organizados em módulos administrativos.",
      items: ["Lotes", "Editais", "Fontes de dados", "Sincronizações"],
    }),
  );
});

await runScene(page, "09", async () => {
  await page.setContent(
    slideHtml({
      kicker: "Visão de futuro",
      title: "Inteligência pública integrada.",
      body: "Uma base para incorporar, de forma autorizada, dados populacionais, econômicos e territoriais.",
      items: ["IPEDF e PDAD", "Mobilidade", "Renda e população", "Serviços do GDF"],
    }),
  );
});

await runScene(page, "10", async () => {
  await page.setContent(
    slideHtml({
      kicker: "Terracap Conecta",
      title: "O lugar certo para uma boa ideia.",
      body: "Território, oportunidade e desenvolvimento conectados em uma experiência simples e transparente.",
      closing: true,
    }),
  );
});

await context.close();
await browser.close();

if (pageErrors.length) {
  throw new Error(`Erros durante a gravação: ${pageErrors.join(" | ")}`);
}

const recordedPath = await video.path();
const targetPath = path.join(outputDirectory, "screen-recording.webm");
await fs.copyFile(recordedPath, targetPath);

console.log(`RECORDING_OK path=${targetPath} scenes=${timeline.scenes.length}`);
