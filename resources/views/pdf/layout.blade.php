@php
    $generatedAt = now('America/Lima');
    $logo = 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path('images/sigla-logo-cropped.png')));
@endphp
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>@yield('title')</title>
<style>
    @page { margin: 32px 32px 64px; }
    body { margin: 0; font-family: "DejaVu Sans", sans-serif; font-size: 10.5px; color: #1f2937; }
    table { border-collapse: collapse; width: 100%; }
    .caja { border: 1px solid #4b5563; border-radius: 4px; }
    .cabecera td { border: 1px solid #4b5563; vertical-align: middle; text-align: center; padding: 10px; }
    .titulo { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; }
    .subtitulo { font-size: 9px; color: #4b5563; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; }
    .numero-rotulo { font-size: 8.5px; font-weight: bold; color: #4b5563; text-transform: uppercase; letter-spacing: 0.8px; }
    .numero { font-size: 17px; font-weight: bold; color: #002f7f; margin-top: 4px; }
    .seccion { font-size: 11px; font-weight: bold; text-transform: uppercase; margin: 0 0 8px; }
    .datos td { padding: 2px 0; vertical-align: top; }
    .datos .rotulo { width: 130px; font-size: 9.5px; font-weight: bold; text-transform: uppercase; }
    .lista th { background: #002f7f; color: #ffffff; border: 1px solid #002f7f; font-size: 9px; font-weight: bold; text-transform: uppercase; text-align: left; padding: 4px 6px; }
    .lista td { border: 1px solid #c9ced6; padding: 3px 6px; }
    .lista tr.par td { background: #f3f5f9; }
    .lista tr.total td { background: #e5e7eb; font-weight: bold; border-top: 1px solid #4b5563; }
    .derecha { text-align: right; }
    .pie { position: fixed; bottom: -44px; left: 0; right: 0; border: 1px solid #4b5563; border-radius: 4px; padding: 8px 12px; font-size: 9.5px; color: #4b5563; }
    .pagina:after { content: "Página " counter(page); }
    .pareja { border-collapse: separate; border-spacing: 14px 0; margin: 14px -14px 0; width: calc(100% + 28px); }
    .pareja > tbody > tr > td { width: 50%; vertical-align: top; border: 1px solid #4b5563; border-radius: 4px; padding: 10px 12px; }
    .sin-corte { page-break-inside: avoid; }
</style>
</head>
<body>
    <div class="pie">
        <table>
            <tr>
                <td>Generado por SIGLA el {{ $generatedAt->format('d/m/Y H:i') }} por {{ $generatedBy }}</td>
                <td class="derecha" style="font-weight: bold; color: #1f2937;"><span class="pagina"></span></td>
            </tr>
        </table>
    </div>

    <table class="cabecera">
        <tr>
            <td style="width: 190px; height: 64px;"><img src="{{ $logo }}" alt="SIGLA, Powered by Macromar" style="max-height: 48px; max-width: 170px;"></td>
            <td>
                <div class="titulo">@yield('heading')</div>
                <div class="subtitulo">@yield('subheading')</div>
            </td>
            <td style="width: 150px; background: #f3f5f9;">
                <div class="numero-rotulo">@yield('number-label')</div>
                <div class="numero">@yield('number')</div>
                @hasSection('number-note')
                    <div class="subtitulo" style="text-transform: none; letter-spacing: 0;">@yield('number-note')</div>
                @endif
            </td>
        </tr>
    </table>

    @yield('content')
</body>
</html>
