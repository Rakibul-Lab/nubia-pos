<?php /** @var int $status */ /** @var string $message */ ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= (int) $status ?> · Nubia Inventory</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { margin:0; font-family:'Inter',sans-serif; min-height:100vh; display:grid; place-items:center;
               background:#0b1020; color:#e8ecf5;
               background-image:radial-gradient(900px 500px at 70% -10%, rgba(139,92,246,.25), transparent 60%); }
        .box { text-align:center; padding:40px; }
        .code { font-size:7rem; font-weight:800; background:linear-gradient(135deg,#0a0a0a,#dc2626);
                -webkit-background-clip:text; background-clip:text; color:transparent; line-height:1; }
        p { color:#9aa6c2; font-size:1.05rem; }
        a { display:inline-block; margin-top:18px; padding:11px 24px; border-radius:12px;
            background:linear-gradient(135deg,#0a0a0a,#dc2626); color:#fff; text-decoration:none; font-weight:600; }
    </style>
</head>
<body>
    <div class="box">
        <div class="code"><?= (int) $status ?></div>
        <p><?= e($message) ?></p>
        <a href="/dashboard">Back to Dashboard</a>
    </div>
</body>
</html>
