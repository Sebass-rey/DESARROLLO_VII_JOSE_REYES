# Proyecto Final – Sistema de Gestión de Estudiantes (PHP)

Este proyecto implementa un sistema completo para gestionar estudiantes usando **arreglos avanzados** y **Programación Orientada a Objetos** en PHP.

## Requisitos implementados
- Clases `Estudiante` y `SistemaGestionEstudiantes` con todos los métodos solicitados.
- Búsqueda parcial e insensible a mayúsculas por nombre/carrera.
- Reporte por materia: promedio, nota más alta y más baja.
- Ranking por promedio (descendente).
- Graduación de estudiantes y listado de graduados.
- Estadísticas por carrera (cantidad, promedio, mejor estudiante).
- Flags automáticos por desempeño: `honor_roll`, `en_riesgo`, `reprobado_alguna`.
- Uso de `array_map`, `array_filter`, `array_reduce`.
- Type hints, manejo de errores, y método `__toString()`.
- **Opcional**: Persistencia JSON (`guardarEnJson`, `cargarDesdeJson`).

## Estructura
- `proyecto_final.php` – Clases, lógica y una **sección de pruebas** al final si se ejecuta por CLI.

## Cómo ejecutar (CLI)
1. Requisitos: PHP 8.0+.
2. Desde la carpeta `TALLER_5`:
   ```bash
   php proyecto_final.php
   ```
   Verás listados, ranking, reportes y una demostración de persistencia JSON.

## Uso básico en código
```php
$sistema = new SistemaGestionEstudiantes();
$e = new Estudiante(1, 'Ana', 20, 'Ingeniería', ['Cálculo' => 95, 'Física' => 88]);
$sistema->agregarEstudiante($e);
echo $sistema->calcularPromedioGeneral();
print_r($sistema->generarRanking());
```

## Persistencia JSON (opcional)
```php
$sistema->guardarEnJson(__DIR__ . '/estudiantes.json');
$sistema->cargarDesdeJson(__DIR__ . '/estudiantes.json');
```

## Entrega (sugerido)
```bash
git add TALLERES/TALLER_5/proyecto_final.php TALLERES/TALLER_5/README.md
git commit -m "Proyecto Final: Sistema de Gestión de Estudiantes completado"
git push origin main
```

---

> Consejo: si no quieres subir cambios de otros talleres, agrega solo los archivos de `TALLER_5` en el `git add`.
