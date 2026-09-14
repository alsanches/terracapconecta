param(
    [Parameter(Mandatory = $true)]
    [string] $OutputDirectory,

    [Parameter(Mandatory = $true)]
    [string] $FfmpegPath
)

$ErrorActionPreference = 'Stop'

Add-Type -AssemblyName System.Speech

$scenes = @(
    @{
        id = '01'
        title = 'O desafio'
        sentences = @(
            'Escolher onde abrir um negócio não depende apenas de encontrar um imóvel disponível.',
            'É preciso compreender a região, o público, a renda, a mobilidade, a vocação comercial e as condições do edital.',
            'Hoje, essas informações podem estar dispersas e exigir várias consultas.'
        )
    },
    @{
        id = '02'
        title = 'A solução'
        sentences = @(
            'O Terracap Conecta foi criado para transformar essa jornada.',
            'A proposta é reunir oportunidades imobiliárias, editais públicos e inteligência territorial em uma única plataforma visual, simples e acessível.'
        )
    },
    @{
        id = '03'
        title = 'Exploração territorial'
        sentences = @(
            'O usuário começa pelo mapa das trinta e cinco Regiões Administrativas do Distrito Federal.',
            'Ao selecionar uma região, o sistema destaca seu território e apresenta as oportunidades disponíveis.',
            'Cada imóvel possui uma ficha com localização, área, destinação, situação da oferta e informações do edital relacionado.'
        )
    },
    @{
        id = '04'
        title = 'Recomendação explicável'
        sentences = @(
            'O diferencial aparece quando o usuário informa o tipo de empreendimento que deseja abrir.',
            'Em vez de mostrar apenas uma lista de imóveis, o Terracap Conecta identifica as oportunidades compatíveis e apresenta um ranking de potencial.',
            'A recomendação é transparente e reproduzível.',
            'Ela considera público-alvo, demanda e densidade, compatibilidade de renda, mobilidade e carência comercial.',
            'Assim, o usuário entende não apenas qual lote foi indicado, mas por que ele recebeu aquela classificação.'
        )
    },
    @{
        id = '05'
        title = 'Templos e assistência social'
        sentences = @(
            'A plataforma também pode atender políticas e programas específicos, como o Igreja Legal.',
            'Neste exemplo, são exibidos dois imóveis históricos obtidos de fonte pública.',
            'Como esses itens tiveram licitação fracassada, o sistema não os apresenta como disponíveis nem calcula uma nota de potencial.',
            'Eles aparecem como referências informativas, com situação, valores e fonte claramente identificados.'
        )
    },
    @{
        id = '06'
        title = 'Editais públicos'
        sentences = @(
            'O Terracap Conecta também oferece uma visão organizada dos editais e chamamentos publicados.',
            'O cidadão pode consultar modalidade, situação, prazos e regiões abrangidas.',
            'Ao abrir os detalhes, encontra as datas relevantes, a última conferência e os links para os documentos e páginas oficiais da Terracap.'
        )
    },
    @{
        id = '07'
        title = 'Requerimento demonstrativo'
        sentences = @(
            'A partir da ficha do imóvel, o usuário pode conhecer como seria o preenchimento de um requerimento.',
            'Nesta versão, a simulação acontece exclusivamente no navegador.',
            'Nenhum dado é enviado ou armazenado e o código gerado não representa um protocolo oficial.',
            'Para uma solicitação real, a plataforma direciona o interessado ao portal oficial da Terracap.'
        )
    },
    @{
        id = '08'
        title = 'Administração e governança'
        sentences = @(
            'Por trás da experiência pública existe uma área administrativa protegida por autenticação.',
            'Nela, equipes autorizadas podem cadastrar lotes, editais, fontes de informação e acompanhar futuras sincronizações.',
            'As regras de publicação ajudam a diferenciar dados demonstrativos, registros oficiais e referências históricas.'
        )
    },
    @{
        id = '09'
        title = 'Evolução futura'
        sentences = @(
            'O protótipo demonstra uma base preparada para evoluir.',
            'Com a integração autorizada a outros órgãos do Governo do Distrito Federal, será possível incorporar indicadores populacionais, renda, mobilidade, serviços e incidência de público-alvo.',
            'O resultado será um instrumento de apoio ao desenvolvimento econômico e ao melhor aproveitamento dos imóveis públicos.'
        )
    },
    @{
        id = '10'
        title = 'Encerramento'
        sentences = @(
            'Terracap Conecta.',
            'Mais do que encontrar um lote, uma nova forma de conectar território, oportunidade e desenvolvimento.',
            'O lugar certo para uma boa ideia.'
        )
    }
)

function Get-AudioDurationMilliseconds {
    param([Parameter(Mandatory = $true)][string] $Path)

    $probe = (& $FfmpegPath -hide_banner -nostats -i $Path -f null NUL 2>&1 | Out-String)

    if ($probe -notmatch 'Duration:\s+(\d{2}):(\d{2}):(\d{2}\.\d+)') {
        throw "Não foi possível determinar a duração de $Path"
    }

    $duration = [TimeSpan]::FromHours([double] $Matches[1]) +
        [TimeSpan]::FromMinutes([double] $Matches[2]) +
        [TimeSpan]::FromSeconds([double]::Parse($Matches[3], [Globalization.CultureInfo]::InvariantCulture))

    return [int] [Math]::Round($duration.TotalMilliseconds)
}

function Format-SrtTime {
    param([Parameter(Mandatory = $true)][int] $Milliseconds)

    $time = [TimeSpan]::FromMilliseconds($Milliseconds)
    return '{0:00}:{1:00}:{2:00},{3:000}' -f [Math]::Floor($time.TotalHours), $time.Minutes, $time.Seconds, $time.Milliseconds
}

New-Item -ItemType Directory -Path $OutputDirectory -Force | Out-Null
$audioDirectory = Join-Path $OutputDirectory 'narration-segments'
New-Item -ItemType Directory -Path $audioDirectory -Force | Out-Null

$synthesizer = [System.Speech.Synthesis.SpeechSynthesizer]::new()
$availableVoices = $synthesizer.GetInstalledVoices() | ForEach-Object { $_.VoiceInfo.Name }

if ($availableVoices -notcontains 'Microsoft Daniel') {
    throw 'A voz masculina Microsoft Daniel não está instalada.'
}

$synthesizer.SelectVoice('Microsoft Daniel')
$synthesizer.Rate = 3
$synthesizer.Volume = 100
$format = [System.Speech.AudioFormat.SpeechAudioFormatInfo]::new(
    22050,
    [System.Speech.AudioFormat.AudioBitsPerSample]::Sixteen,
    [System.Speech.AudioFormat.AudioChannel]::Mono
)

$introSilencePath = Join-Path $audioDirectory 'silence-intro.wav'
$sentenceSilencePath = Join-Path $audioDirectory 'silence-sentence.wav'
$sceneSilencePath = Join-Path $audioDirectory 'silence-scene.wav'

& $FfmpegPath -y -hide_banner -loglevel error -f lavfi -i 'anullsrc=r=22050:cl=mono' -t 1.2 -c:a pcm_s16le $introSilencePath
& $FfmpegPath -y -hide_banner -loglevel error -f lavfi -i 'anullsrc=r=22050:cl=mono' -t 0.24 -c:a pcm_s16le $sentenceSilencePath
& $FfmpegPath -y -hide_banner -loglevel error -f lavfi -i 'anullsrc=r=22050:cl=mono' -t 0.75 -c:a pcm_s16le $sceneSilencePath

$cursor = 1200
$subtitleIndex = 1
$subtitleLines = [Collections.Generic.List[string]]::new()
$concatLines = [Collections.Generic.List[string]]::new()
$timelineScenes = [Collections.Generic.List[object]]::new()

$concatLines.Add("file '$($introSilencePath.Replace('\', '/'))'")

try {
    foreach ($scene in $scenes) {
        $sceneStart = $cursor
        $sentenceNumber = 0

        foreach ($sentence in $scene.sentences) {
            $sentenceNumber++
            $segmentPath = Join-Path $audioDirectory ("scene-{0}-{1:00}.wav" -f $scene.id, $sentenceNumber)

            $synthesizer.SetOutputToWaveFile($segmentPath, $format)
            $synthesizer.Speak($sentence)
            $synthesizer.SetOutputToNull()

            $duration = Get-AudioDurationMilliseconds -Path $segmentPath
            $sentenceStart = $cursor
            $sentenceEnd = $cursor + $duration

            $subtitleLines.Add([string] $subtitleIndex)
            $subtitleLines.Add("$(Format-SrtTime $sentenceStart) --> $(Format-SrtTime $sentenceEnd)")
            $subtitleLines.Add($sentence)
            $subtitleLines.Add('')

            $concatLines.Add("file '$($segmentPath.Replace('\', '/'))'")
            $cursor = $sentenceEnd
            $subtitleIndex++

            if ($sentenceNumber -lt $scene.sentences.Count) {
                $concatLines.Add("file '$($sentenceSilencePath.Replace('\', '/'))'")
                $cursor += 240
            }
        }

        $concatLines.Add("file '$($sceneSilencePath.Replace('\', '/'))'")
        $cursor += 750

        $timelineScenes.Add([pscustomobject]@{
            id = $scene.id
            title = $scene.title
            start_ms = $sceneStart
            end_ms = $cursor
            duration_ms = $cursor - $sceneStart
        })
    }
}
finally {
    $synthesizer.Dispose()
}

$concatPath = Join-Path $OutputDirectory 'narration-concat.txt'
$subtitlePath = Join-Path $OutputDirectory 'captions.srt'
$timelinePath = Join-Path $OutputDirectory 'timeline.json'
$narrationPath = Join-Path $OutputDirectory 'narration.wav'

$concatLines | Set-Content -LiteralPath $concatPath -Encoding utf8
$subtitleLines | Set-Content -LiteralPath $subtitlePath -Encoding utf8

[pscustomobject]@{
    intro_ms = 1200
    total_ms = $cursor
    voice = 'Microsoft Daniel'
    rate = 3
    scenes = $timelineScenes
} | ConvertTo-Json -Depth 5 | Set-Content -LiteralPath $timelinePath -Encoding utf8

& $FfmpegPath -y -hide_banner -loglevel error -f concat -safe 0 -i $concatPath -c:a pcm_s16le $narrationPath

Write-Output "NARRATION_OK duration_ms=$cursor voice=Microsoft Daniel"
