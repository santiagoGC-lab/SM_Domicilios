# Sistema de Manejo de Historial por Meses

## Descripción General

El sistema de historial de pedidos ha sido actualizado para manejar los registros por meses en lugar de por días. Esto permite una mejor organización y gestión de los datos históricos.

## Cambios Implementados

### 1. Vista de Historial (`vistas/historial_pedidos.php`)

- **Tarjeta de ingresos**: Cambiada de "Ingresos del Día" a "Ingresos del Mes"
- **Filtros por defecto**: Los filtros de fecha ahora muestran por defecto el mes actual completo
- **Cálculo de ingresos**: Se calculan los ingresos del mes actual en lugar del día actual
- **Descripción**: Se agregó una descripción explicativa sobre el manejo por meses

### 2. Servicio de Historial (`servicios/historial_pedidos.php`)

- **Filtros mejorados**: Los filtros de fecha funcionan correctamente con el nuevo sistema
- **Compatibilidad**: Mantiene toda la funcionalidad existente de búsqueda y paginación

### 3. Script de Migración (`servicios/migrar_pedidos_mensuales.php`)

- **Migración por meses**: Ahora migra pedidos del mes anterior completo en lugar de solo los de más de 7 días
- **Mejor manejo de errores**: Incluye try-catch para manejar errores durante la migración
- **Información detallada**: Proporciona información sobre el proceso de migración

### 4. Script de Verificación (`servicios/verificar_migracion_mensual.php`)

- **Nuevo script**: Permite verificar el estado del sistema de migración mensual
- **Información completa**: Muestra estadísticas de pedidos del mes actual, anterior y ya migrados
- **Recomendaciones**: Proporciona recomendaciones sobre cuándo ejecutar la migración

## Flujo de Trabajo

### Mes Actual
1. Los pedidos se archivan en `historico_pedidos` cuando se completan
2. La vista de Historial muestra solo los pedidos del mes actual
3. Los ingresos se calculan para el mes actual completo

### Fin de Mes
1. Al inicio del nuevo mes, se ejecuta el script de migración
2. Los pedidos del mes anterior se mueven a `pedidos_mensuales`
3. Los pedidos migrados se eliminan de `historico_pedidos`
4. Los pedidos migrados están disponibles en la sección de Reportes

## Comandos Útiles

### Verificar Estado del Sistema
```bash
php servicios/verificar_migracion_mensual.php
```

### Ejecutar Migración Manual
```bash
php servicios/migrar_pedidos_mensuales.php
```

## Configuración Automática (Opcional)

Para automatizar la migración, puede configurar un cron job que se ejecute el primer día de cada mes:

```bash
# Ejecutar el primer día de cada mes a las 2:00 AM
0 2 1 * * /ruta/a/php /ruta/a/SM_Domicilios/servicios/migrar_pedidos_mensuales.php
```

## Beneficios del Nuevo Sistema

1. **Mejor organización**: Los datos se organizan por meses completos
2. **Rendimiento mejorado**: El historial actual es más liviano al contener solo el mes actual
3. **Reportes históricos**: Los datos antiguos están disponibles en Reportes para análisis
4. **Gestión automática**: El proceso de migración puede automatizarse
5. **Claridad**: Es más claro qué datos están en cada sección

## Notas Importantes

- Los pedidos del mes actual permanecen en Historial hasta el final del mes
- Los pedidos del mes anterior se mueven automáticamente a Reportes
- El sistema mantiene toda la funcionalidad de búsqueda y filtros
- Los ingresos se calculan correctamente para el mes actual
- La migración es segura y mantiene la integridad de los datos

## Solución de Problemas

### Si la migración falla
1. Verifique los permisos de la base de datos
2. Ejecute el script de verificación para diagnosticar problemas
3. Revise los logs de error de PHP

### Si los filtros no funcionan
1. Verifique que las fechas estén en formato correcto (YYYY-MM-DD)
2. Asegúrese de que el JavaScript esté cargando correctamente
3. Revise la consola del navegador para errores

### Si los ingresos no se calculan correctamente
1. Verifique que los pedidos tengan el estado correcto ('entregado')
2. Confirme que las fechas de los pedidos estén en el formato correcto
3. Ejecute el script de verificación para ver las estadísticas 