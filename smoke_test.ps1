param([string]$base = 'http://127.0.0.1:8899')
$ErrorActionPreference = 'SilentlyContinue'
$login = Invoke-WebRequest -Uri "$base/login" -SessionVariable s -UseBasicParsing
$token = ([regex]'name="_token" value="([^"]+)"').Match($login.Content).Groups[1].Value
$null = Invoke-WebRequest -Uri "$base/login" -Method POST -Body @{ _token=$token; email='admin@nubia.test'; password='admin123' } -WebSession $s -UseBasicParsing -MaximumRedirection 0
$paths = $args
if (-not $paths) {
  $paths = @('/dashboard','/products','/products/create','/products/1','/categories','/brands','/units','/warehouses',
             '/customers','/suppliers','/purchases','/sales','/pos','/direct-buy-sell','/expenses','/reports',
             '/accounting/cash-book','/users','/roles','/settings','/profile','/activity-logs','/quotations',
             '/stock','/customers/create','/suppliers/create','/purchases/create','/sales/create','/direct-buy-sell/create')
}
foreach ($p in $paths) {
  try {
    $r = Invoke-WebRequest -Uri "$base$p" -WebSession $s -UseBasicParsing -MaximumRedirection 0
    Write-Output ("{0,-32} {1} {2}b" -f $p, $r.StatusCode, $r.Content.Length)
  } catch {
    $code = $_.Exception.Response.StatusCode.value__
    $msg = ''
    if ($_.Exception.Response) { $sr=(New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())).ReadToEnd(); $msg = ($sr -replace '\s+',' ').Substring(0,[Math]::Min(220,$sr.Length)) }
    Write-Output ("{0,-32} ERR {1} :: {2}" -f $p, $code, $msg)
  }
}
