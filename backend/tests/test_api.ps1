# =====================================================================
# Script de Pruebas API - Módulos de Eventos, Reseñas y Reportes
# =====================================================================

$baseUrl = "http://localhost:8000/api"
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host " INICIANDO PRUEBAS DE LA API" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

# 1. Login simulado
Write-Host "`n[1/4] Iniciando sesion como Organizador (ID: 1)..." -ForegroundColor Yellow
$login = Invoke-RestMethod -Uri "$baseUrl/dev-login.php?usuario_id=1" -WebSession $session
Write-Host $login.message -ForegroundColor Green

# 2. Crear evento
Write-Host "`n[2/4] Creando evento de prueba..." -ForegroundColor Yellow
$bodyEvento = @{
    titulo = "Test Automatizado ESPOL"
    descripcion = "Evento generado automaticamente por el script de pruebas."
    fecha_evento = "2026-12-01"
    hora_evento = "10:00"
    lugar = "Auditorio FIEC"
    categoria_id = 1
    aforo_maximo = 50
} | ConvertTo-Json

$evento = Invoke-RestMethod -Uri "$baseUrl/eventos/crear.php" -Method Post -Body $bodyEvento -ContentType "application/json; charset=utf-8" -WebSession $session
$nuevoEventoId = $evento.data.id
Write-Host "Evento creado con exito. ID asignado: $nuevoEventoId" -ForegroundColor Green

# 3. Crear reseña (Tu módulo)
Write-Host "`n[3/4] Enviando resena al evento $nuevoEventoId..." -ForegroundColor Yellow
$bodyResena = @{
    evento_id = $nuevoEventoId
    calificacion = 5
    comentario = "Prueba de reseña automatizada. ¡Todo funciona perfecto!"
} | ConvertTo-Json

$resena = Invoke-RestMethod -Uri "$baseUrl/resenas/crear.php" -Method Post -Body $bodyResena -ContentType "application/json; charset=utf-8" -WebSession $session
Write-Host $resena.message -ForegroundColor Green

# 4. Generar reporte (Tu módulo)
Write-Host "`n[4/4] Solicitando reporte estadistico del evento $nuevoEventoId..." -ForegroundColor Yellow
$reporte = Invoke-RestMethod -Uri "$baseUrl/eventos/reporte.php?evento_id=$nuevoEventoId" -WebSession $session

Write-Host "Resultados del reporte:" -ForegroundColor Green
Write-Host " - Total Inscritos: $($reporte.data.total_inscritos)"
Write-Host " - Asistencia Final: $($reporte.data.asistencia_final)"
Write-Host " - Porcentaje: $($reporte.data.porcentaje_asistencia)"
Write-Host " - Valoracion Promedio: $($reporte.data.valoracion_promedio)"

Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host " PRUEBAS FINALIZADAS CORRECTAMENTE" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan