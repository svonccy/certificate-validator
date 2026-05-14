<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificacion de certificado</title>
</head>
<body>
    <main>
        <h1>Verificacion de certificado</h1>
        <p><strong>ID:</strong> {{ $certificado->id }}</p>
        <p><strong>Estado:</strong> {{ $certificado->estado === 'VALIDO' ? 'Valido' : 'Pendiente' }}</p>
        <p><strong>DNI:</strong> {{ $certificado->dni_titular }}</p>
        <p><strong>Titular:</strong> {{ $certificado->nombre_titular }}</p>
        <p><strong>Tipo:</strong> {{ $certificado->tipo_certificado }}</p>
    </main>
</body>
</html>
