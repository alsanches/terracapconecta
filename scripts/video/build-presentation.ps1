param(
    [string] $OutputDirectory = (Join-Path $PSScriptRoot '..\..\artifacts\terracap-video'),

    [switch] $ReuseRecording
)

$ErrorActionPreference = 'Stop'

$OutputDirectory = [IO.Path]::GetFullPath($OutputDirectory)
$ffmpegPath = Join-Path $env:LOCALAPPDATA 'Temp\terracap-video-tools\node_modules\ffmpeg-static\ffmpeg.exe'
$narrationScript = Join-Path $PSScriptRoot 'generate-narration.ps1'
$recordingScript = Join-Path $PSScriptRoot 'record-presentation.mjs'

if (-not (Test-Path -LiteralPath $ffmpegPath)) {
    throw "FFmpeg não encontrado em $ffmpegPath"
}

New-Item -ItemType Directory -Path $OutputDirectory -Force | Out-Null

if (-not $ReuseRecording) {
    & $narrationScript -OutputDirectory $OutputDirectory -FfmpegPath $ffmpegPath
    & node $recordingScript $OutputDirectory
}

$screenRecording = Join-Path $OutputDirectory 'screen-recording.webm'
$narration = Join-Path $OutputDirectory 'narration.wav'
$captions = Join-Path $OutputDirectory 'captions.srt'
$finalVideo = Join-Path $OutputDirectory 'Terracap-Conecta-Apresentacao.mp4'
$subtitlePath = $captions.Replace('\', '/').Replace(':', '\:')
$subtitleFilter = "subtitles='$subtitlePath':force_style='FontName=Arial,FontSize=16,PrimaryColour=&H00FFFFFF,OutlineColour=&H80000000,BackColour=&H50000000,BorderStyle=3,Outline=1,Shadow=0,MarginV=28,Alignment=2'"

& $ffmpegPath -y -hide_banner -loglevel warning `
    -i $screenRecording `
    -i $narration `
    -vf $subtitleFilter `
    -af 'loudnorm=I=-16:TP=-1.5:LRA=11' `
    -c:v libx264 `
    -preset medium `
    -crf 21 `
    -pix_fmt yuv420p `
    -c:a aac `
    -b:a 192k `
    -shortest `
    -movflags +faststart `
    $finalVideo

if (-not (Test-Path -LiteralPath $finalVideo)) {
    throw 'O vídeo final não foi criado.'
}

$inspection = (& $ffmpegPath -hide_banner -i $finalVideo 2>&1 | Out-String)

if ($inspection -notmatch 'Video:\s+h264' -or $inspection -notmatch 'Audio:\s+aac' -or $inspection -notmatch '1920x1080') {
    throw "O vídeo final não contém os fluxos esperados.`n$inspection"
}

$hash = (Get-FileHash -LiteralPath $finalVideo -Algorithm SHA256).Hash.ToLowerInvariant()
$size = (Get-Item -LiteralPath $finalVideo).Length

Write-Output "VIDEO_OK path=$finalVideo bytes=$size sha256=$hash"
