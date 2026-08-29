$files = Get-ChildItem -Path "d:\xampp1\htdocs\gymora\gymora_backend\resources\views" -Filter "*.blade.php" -Recurse | Where-Object { $_.FullName -notmatch "\\auth\\" -and $_.FullName -notmatch "\\partials\\" -and $_.Name -ne "dashboard.blade.php" -and $_.Name -ne "login.blade.php" -and $_.Name -ne "register.blade.php" }

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    
    # Replace hardcoded purple hex codes with tailwind indigo classes
    $content = $content -replace "bg-\[#8122db\]", "bg-indigo-600"
    $content = $content -replace "text-\[#8122db\]", "text-indigo-600"
    $content = $content -replace "border-\[#8122db\]", "border-indigo-600"
    $content = $content -replace "ring-\[#8122db\]", "ring-indigo-600"
    $content = $content -replace "from-\[#8122db\]", "from-indigo-600"
    
    # Gradient ends
    $content = $content -replace "to-\[#d9229b\]", "to-indigo-400"
    
    # Hover states
    $content = $content -replace "bg-\[#6c1ab8\]", "bg-indigo-700"
    $content = $content -replace "hover:bg-\[#6c1ab8\]", "hover:bg-indigo-700"
    
    # Shadows
    $content = $content -replace "shadow-purple-900/20", "shadow-indigo-600/20"
    $content = $content -replace "shadow-purple-900/30", "shadow-indigo-600/30"
    
    # Light purple backgrounds and text
    $content = $content -replace "bg-purple-50", "bg-indigo-50"
    $content = $content -replace "bg-purple-100", "bg-indigo-100"
    $content = $content -replace "hover:bg-purple-100", "hover:bg-indigo-100"
    $content = $content -replace "text-purple-700", "text-indigo-700"
    $content = $content -replace "text-purple-600", "text-indigo-600"
    $content = $content -replace "text-purple-500", "text-indigo-500"
    
    Set-Content -Path $file.FullName -Value $content -NoNewline
    Write-Host "Updated $($file.Name)"
}
