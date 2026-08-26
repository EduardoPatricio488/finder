<!doctype html>
<html lang="pt-PT">
    <body style="margin:0;background:#f7f4ee;color:#1c1917;font-family:Arial,sans-serif;padding:32px">
        <div style="max-width:560px;margin:auto;background:#fff;padding:36px;border-radius:12px">
            <p style="color:#b45309;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase">Casa &amp; Co.</p>
            <h1 style="font-size:26px;margin:20px 0 12px">{{ $headline }}</h1>
            <p style="font-size:16px;line-height:1.7;color:#57534e">{{ $bodyText }}</p>
            @if ($orderNumber)
                <p style="margin-top:28px;padding:16px;background:#fef3c7;border-radius:8px;font-weight:bold">Encomenda: #{{ $orderNumber }}</p>
            @endif
            <p style="margin-top:32px;font-size:13px;color:#78716c">Obrigado por escolher a Casa &amp; Co.</p>
        </div>
    </body>
</html>
