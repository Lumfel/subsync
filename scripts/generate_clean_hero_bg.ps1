Add-Type -AssemblyName System.Drawing

$mockupPath = "C:\Users\user\.gemini\antigravity\brain\4f5b1360-7d74-41e9-9cfa-6271bbf6f0a0\.user_uploaded\media_1794818299430_03d6ffd7.jpg"
$housesPath = "C:\laragon\www\subsync\public\images\admin_hero_bg_day.jpg"

$srcMockup = [System.Drawing.Bitmap]::FromFile($mockupPath)
$srcHouses = [System.Drawing.Bitmap]::FromFile($housesPath)

$width = 1200
$height = 480
$out = New-Object System.Drawing.Bitmap($width, $height)
$g = [System.Drawing.Graphics]::FromImage($out)
$g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::None

# 1. Fill entire canvas with solid cream #FAF6F0
$cream = [System.Drawing.Color]::FromArgb(250, 246, 240)
$creamBrush = New-Object System.Drawing.SolidBrush($cream)
$g.FillRectangle($creamBrush, 0, 0, $width, $height)

# 2. Draw subdivision houses strictly on the right side (from x: 520 to 1200)
# admin_hero_bg_day.jpg is 1024x442
$housesSrc = New-Object System.Drawing.Rectangle(240, 0, 784, 442)
$housesDest = New-Object System.Drawing.Rectangle(520, 0, 680, $height)
$g.DrawImage($srcHouses, $housesDest, $housesSrc, [System.Drawing.GraphicsUnit]::Pixel)

# 3. Draw entrance sign on the far right (x: 1010 to 1200)
$signW = 190
$signH = 480
$signBmp = New-Object System.Drawing.Bitmap($signW, $signH)
$sg = [System.Drawing.Graphics]::FromImage($signBmp)
$sg.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic

$srcSignRect = New-Object System.Drawing.Rectangle(865, 48, 159, 451)
$destSignRect = New-Object System.Drawing.Rectangle(0, 0, $signW, $signH)
$sg.DrawImage($srcMockup, $destSignRect, $srcSignRect, [System.Drawing.GraphicsUnit]::Pixel)

# Feather left 40px of sign into houses
for ($x = 0; $x -lt 40; $x++) {
    $alpha = [int](($x / 40.0) * 255)
    for ($y = 0; $y -lt $signH; $y++) {
        $p = $signBmp.GetPixel($x, $y)
        $hPixel = $out.GetPixel((1200 - $signW) + $x, $y)
        $r = [int](($p.R * $alpha + $hPixel.R * (255 - $alpha)) / 255)
        $gCol = [int](($p.G * $alpha + $hPixel.G * (255 - $alpha)) / 255)
        $b = [int](($p.B * $alpha + $hPixel.B * (255 - $alpha)) / 255)
        $signBmp.SetPixel($x, $y, [System.Drawing.Color]::FromArgb(255, $r, $gCol, $b))
    }
}
$sg.Dispose()

$g.DrawImage($signBmp, (1200 - $signW), 0)
$signBmp.Dispose()

# 4. Smooth, buttery gradient fade on the houses from x: 520 to 740:
# At x=520, it is 100% solid cream #FAF6F0
# At x=740, it is 100% photo
for ($x = 0; $x -lt 220; $x++) {
    $alpha = [int]((1.0 - [Math]::Pow($x / 220.0, 1.25)) * 255)
    for ($y = 0; $y -lt $height; $y++) {
        $p = $out.GetPixel(520 + $x, $y)
        $r = [int](($cream.R * $alpha + $p.R * (255 - $alpha)) / 255)
        $gCol = [int](($cream.G * $alpha + $p.G * (255 - $alpha)) / 255)
        $b = [int](($cream.B * $alpha + $p.B * (255 - $alpha)) / 255)
        $out.SetPixel(520 + $x, $y, [System.Drawing.Color]::FromArgb(255, $r, $gCol, $b))
    }
}

# 5. Overwrite 0..520 with solid cream #FAF6F0 just to ensure byte-perfect consistency
$g.FillRectangle($creamBrush, 0, 0, 521, $height)

$creamBrush.Dispose()
$g.Dispose()
$srcMockup.Dispose()
$srcHouses.Dispose()

# Save with 96% JPEG quality
$destPath = "C:\laragon\www\subsync\public\images\terranova_hero_bg.jpg"
$encoder = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.MimeType -eq "image/jpeg" }
$encParams = New-Object System.Drawing.Imaging.EncoderParameters(1)
$encParams.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter([System.Drawing.Imaging.Encoder]::Quality, [long]96)

$out.Save($destPath, $encoder, $encParams)
$out.Dispose()

Write-Host "Done generating perfect terranova_hero_bg.jpg"
