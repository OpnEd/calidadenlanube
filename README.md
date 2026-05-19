# Documentación - MCE / Medicamentos de control especial

### Asignación de roles

Se debe asignar un solo rol a cada usuario y a cada rol asignar los permisos necesarios. No asignar más de un rol a un usuario.

### Trait HasTeamRoles

El trait HasTeamRoles agrega dos métodos útiles para manejar roles y permisos de usuarios en equipos (teams) dentro de una aplicación multi-tenant (multi-equipo):

1. getCurrentTeamRole()
¿Qué hace?
Obtiene el rol del usuario para el equipo (tenant) actual.
¿Cómo lo hace?
Usa Filament::getTenant() para obtener el equipo actual.
Si no hay equipo, retorna null y registra un warning en el log.
Busca el primer rol del usuario que esté asociado a ese equipo (usando la relación roles() y filtrando por team_id).
Permite que el rol sea global (roles.team_id nulo) o específico del equipo.
2. hasTeamPermission($permissionName)
¿Qué hace?
Verifica si el rol del usuario para el equipo actual tiene un permiso específico.
¿Cómo lo hace?
Llama a getCurrentTeamRole() para obtener el rol del usuario en el equipo actual.
Si hay rol, revisa si ese rol tiene el permiso con el nombre dado ($permissionName).

# Migraciones

El campo 'team_id' de la tabla 'model_has_roles' debe ser transformado para que acepte NULL para poder que funcion el 'Super-Admin'.

# Export y data base notifications por team_id

La exportación de datos envía trabajos a colas y genera database notifications. Se debió
- adicionar 'team_id' a la tabla 'notifications'
- Crear el modelo 'TeamNotification' que extiente a 'use Illuminate\Notifications\DatabaseNotification as BaseNotification'
- Cear el 'EnsureTeamContext' middleware
- modificar el exporter ProductExporter para filtrar datos por 'team_i'
- se sobreescribieron los métodos de notificaciones en el modelo User

---

# Esquema de Documentación del Proyecto (Propuesta Comercial)

A continuación se presenta el esquema detallado de la documentación funcional y técnica del sistema **MCE**, estructurado para demostrar la capacidad, seguridad y escalabilidad de la solución a clientes corporativos.

## 1. Resumen Ejecutivo
*   **Nombre del Producto:** MCE (Medicamentos de Control Especial).
*   **Objetivo:** Proveer una plataforma unificada para la gestión farmacéutica, clínica y educativa, asegurando la trazabilidad total de insumos sensibles.
*   **Diferenciadores:** Integración nativa entre inventario clínico y comercial, módulo de capacitación (LMS) integrado para cumplimiento normativo, y arquitectura multi-sede.

## 2. Arquitectura Técnica y Seguridad
*   **Core:** Desarrollado sobre **Laravel** y **Filament PHP**, garantizando robustez y una interfaz de usuario moderna.
*   **Multi-Tenancy:** Arquitectura diseñada para soportar múltiples organizaciones o sedes de forma aislada y segura.
*   **Control de Acceso (RBAC):** Sistema avanzado de Roles y Permisos (`HasTeamRoles`) que permite definir accesos específicos por sede (ej. Farmacéutico, Médico, Administrador).

## 3. Módulos Principales

### A. Gestión de Inventarios y Logística
*   **Trazabilidad de Lotes:** Control estricto de fechas de vencimiento y números de lote para cumplimiento sanitario.
*   **Categorización Flexible:** Soporte para Medicamentos (Antibióticos, Controlados), Dispositivos Médicos y Reactivos.
*   **Búsqueda Avanzada:** Localización rápida por código de barras, nombre o lote específico.

### B. Punto de Venta (POS) y Facturación
*   **Venta Rápida:** Interfaz optimizada para mostradores de farmacia con búsqueda predictiva.
*   **Carrito de Compras:** Gestión de sesiones de venta, cálculo de totales y validación de stock disponible en tiempo real.
*   **Facturación Integrada:** Generación automática de facturas (`Invoices`) vinculadas a las ventas y clientes.
*   **Gestión de Clientes:** Base de datos de pacientes/clientes con historial de compras.

### C. Capacitación y Calidad (LMS)
*   **Plataforma de E-Learning:** Módulo para la capacitación continua del personal en protocolos y manejo de medicamentos.
*   **Estructura de Cursos:** Organización en Módulos y Lecciones.
*   **Evaluaciones Automatizadas:**
    *   Cuestionarios de opción múltiple y falso/verdadero.
    *   Calificación automática y retroalimentación inmediata.
    *   Registro de intentos y certificación de aprobación.

### D. Gestión Clínica
*   **Hojas de Anestesia:** Digitalización del consumo de anestésicos en quirófano.

**Pasos para la creación de una hoja de Anestesia:**

Para crear exitosamente una hoja de anestesia debemos garantizar previamente algunos datos
en la base de datos:

1. Cliente o propietario
2. Paciente
3. Productos en el intentario

3.1. Para ingresar productos al inventario se debe crear primero los lotes de los productos,
y para esto se debe tener previamente fabricantes y registros sanitarios. Estos dos últimos,
son datos globales que deben ser poblados por el proveedor del SaaS.

4. Usuarios con roles de Director Técnico o médico y estar marcado como cirujano.

***Procedimiento***
a. En el menú principal a la izquierda de la pantalla, clickear 'Hojas de Anestesia'.
Esta acción lo llevará al índex de hojas de anestesia, donde podrá ver detalles de cada una clickeando
sobre la fila correspondiente, o clickeando el botón 'Editar' que está dentro de los tres puntitos
verticales al final de cada fila.
b. En la cabecera del index o lista de hojas de anestesia, a la derecha, se encuentra el botón para crear nuevas hojas, clickearlo. Esta acción abre un modal en el cual deberá escoger el consecutivo del recetario que quiere asociar a esta hoja de anestesia; inicialmente este consecutivo de recetario tiene un estado de 'available', pero al ser asociado a una hoja pasa a estar en 'in_use', por lo cual ya no aparecerá en la lista de consecutivos a seleccionar cuando emerge el modal; al clickear el botón de crear, se creará la hoja de anestesia en estado 'opened', y será redirigido a la hoja en sí, que es un formulario en el que deberá diligenciar la información pertinente así como asociar medicamentos y sus cantidades.
c. Una vez en el formulario de edición deberá seleccionar el propietario, el paciente, el médico que está operando, la  hora de inicio de la anestesia.
d. Diligenciar la anamnesis y las notas de anestesia. Estos campos funcionan como una pequeña tabla de dos columnas. En la primera columna escribes el nombre o concepto, y en la segunda el dato correspondiente. Ejm:
+----------------+----------+
| ***Característica*** | ***Valor***    |
+----------------+----------+
| Color          | Rojo     |
+----------------+----------+
| Peso           | 2 Kg     |
+----------------+----------+
e. En la sección inferior, ***ítems de hoja de anesthesia***, debe asociar los medicamentos y sus cantidades, y las etapas del ciclo quirúrgico en que fueron administrados cada uno de ellos. Es decir que puede haber múltiples adiciones de un mismo producto y de hecho, esto es lo recomendable, en lugar de ir incrementando el valor en los casos en que se hacen múltiples administraciones.
f. cuando se haya finalizado la cirugía se diligencia la hora de finalización, se verifica que toda la información esté correcta, y se da click en la esquina superior derecha 'confirmar y cerrar hoja de anestesia'; con esta acción, de manera automática se consolidan las cantidades totales de cada medicamento administrado, y se descuentan del inventario. El estado de la hoja cambia de 'opened' a 'closed' y la hoja se bloquea, pudiendo ser modificada mediante adición de historial de cambios.
g. Si se da click al botón 'cancelar' estando en el formulario de edición en estado 'opened', emerge un modal que pregunta la razón de la cancelación. Al dar click en 'cancelar' se cierra el modal, el estado de la hoja pasa de 'opened' a 'closed', y el consecutivo asociado pasa de estado 'in_use' a estado 'available' y a estar nuevamente presente en la lista de consecutivos seleccionables para asociar a hoja de anestesia.


*   **Consumo Inteligente:** Descargo automático del inventario al cerrar la hoja de anestesia (Observer Pattern), reduciendo errores humanos.

## 4. Flujos de Trabajo Automatizados
1.  **Venta en Mostrador:** Selección de Lote -> Validación de Stock -> Asignación de Cliente -> Generación de Factura.
2.  **Consumo Quirúrgico:** Apertura de Hoja -> Registro de Dosis -> Cierre de Hoja -> Decremento de Inventario.
3.  **Certificación de Personal:** Inscripción en Curso -> Progreso de Lecciones -> Aprobación de Assessment -> Habilitación Operativa.

## 5. Escalabilidad y Mantenimiento
*   **Notificaciones:** Sistema de alertas en base de datos y colas de trabajo para exportación de reportes masivos.
*   **Auditoría:** Trazabilidad de acciones críticas (creación de ventas, ajustes de inventario).
*   **Migraciones y Datos:** Estructura de base de datos optimizada y seeders preconfigurados para despliegue rápido.


**Mapa De Trabajo**
**Iteración A: cerrar huecos críticos**
- `A1. Bloquear bypass desde admin`
  Archivos: [app/Filament/Resources/PurchaseResource.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/Resources/PurchaseResource.php:151)
  Cambios: quitar o esconder las acciones `receive` y `clone_to_reception` del panel `admin`, o moverlas detrás de una verificación estricta que exija despacho completado.
  Resultado esperado: desde `admin` la compra no puede saltar de `confirmed` a `delivered` sin pasar por `tenantManager`.

- `A2. Evitar despachos duplicados`
  Archivos: [app/Services/DispatchService.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Services/DispatchService.php:18), [database/migrations/2025_05_21_190850_create_dispatches_table.php](C:/Users/PAOLA/Herd/mce1.0.0/database/migrations/2025_05_21_190850_create_dispatches_table.php:17)
  Cambios: agregar índice único a `dispatches.purchase_id`; en el servicio validar `dispatch()->exists()` y usar transacción.
  Resultado esperado: una compra solo puede tener un despacho.

- `A3. Bloquear CRUD peligroso de dispatch`
  Archivos: [app/Filament/TenantManager/Resources/DispatchResource.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/TenantManager/Resources/DispatchResource.php:29), [app/Filament/TenantManager/Resources/DispatchResource/Pages/CreateDispatch.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/TenantManager/Resources/DispatchResource/Pages/CreateDispatch.php:9), [app/Filament/TenantManager/Resources/DispatchResource/Pages/EditDispatch.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/TenantManager/Resources/DispatchResource/Pages/EditDispatch.php:13)
  Cambios: deshabilitar `CreateDispatch`, ocultar `purchase_id` y `team_id` o volverlos solo lectura, quitar `Delete/ForceDelete/Restore` del flujo normal.
  Resultado esperado: el despacho nace solo desde una `Purchase` confirmada.

- `A4. Corregir policies faltantes`
  Archivos: [app/Providers/AuthServiceProvider.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Providers/AuthServiceProvider.php:28), [app/Policies/DispatchPolicy.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Policies/DispatchPolicy.php:16), [app/Policies/PurchaseItemPolicy.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Policies/PurchaseItemPolicy.php:22)
  Cambios: registrar `DispatchPolicy`; reemplazar los `return true` de `PurchaseItemPolicy` por reglas reales atadas a tenant y estado.
  Resultado esperado: no dependemos solo de visibilidad de botones.

**Iteración B: volver consistente la lógica de negocio**
- `B1. Rehacer confirmación de compra`
  Archivos: [app/Filament/Resources/PurchaseResource.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/Resources/PurchaseResource.php:134), [app/Filament/Resources/PurchaseResource/RelationManagers/ItemsRelationManager.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/Resources/PurchaseResource/RelationManagers/ItemsRelationManager.php:122), [app/Models/Purchase.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Models/Purchase.php:86)
  Cambios: llevar la confirmación a un servicio dedicado; recalcular total en backend; validar que haya items y precios válidos antes de confirmar.
  Resultado esperado: una sola forma confiable de pasar a `confirmed`.

- `B2. Corregir lookup de precios`
  Archivos: [app/Filament/Resources/PurchaseResource/Pages/ListPurchases.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/Resources/PurchaseResource/Pages/ListPurchases.php:68), [app/Filament/Resources/PurchaseResource/RelationManagers/ItemsRelationManager.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/Resources/PurchaseResource/RelationManagers/ItemsRelationManager.php:49)
  Cambios: reemplazar `CentralProductPrice::find($productId)` por consulta por `product_id`.
  Resultado esperado: cotizaciones y pedidos usan el precio correcto.

- `B3. Hacer transaccional e idempotente la creación del dispatch`
  Archivos: [app/Services/DispatchService.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Services/DispatchService.php:18)
  Cambios: usar `DB::transaction()`, bloquear la `Purchase`, validar estado, validar inexistencia de despacho y crear items hijos dentro de la misma transacción.
  Resultado esperado: sin registros parciales ni carreras.

- `B4. Separar creación de dispatch de descuento de stock`
  Archivos: [app/Services/DispatchService.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Services/DispatchService.php:35), [app/Observers/DispatchItemsObserver.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Observers/DispatchItemsObserver.php:10)
  Cambios: no crear `dispatch_items` con lote `null` si eso dispara stock; mover descuento a una acción explícita de asignación/finalización o endurecer el observer para no tocar stock hasta que `batch_id` exista.
  Resultado esperado: el stock solo cambia cuando hay lote y cantidad válidos.

**Iteración C: alinear esquema y estados**
- `C1. Unificar modelo de lote central`
  Archivos: [database/migrations/2025_05_21_190902_create_dispatch_items_table.php](C:/Users/PAOLA/Herd/mce1.0.0/database/migrations/2025_05_21_190902_create_dispatch_items_table.php:17), [app/Models/DispatchItems.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Models/DispatchItems.php:38), [app/Filament/TenantManager/Resources/DispatchResource/RelationManagers/ItemsRelationManager.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/TenantManager/Resources/DispatchResource/RelationManagers/ItemsRelationManager.php:23)
  Cambios: migración correctiva para que `batch_id` apunte a `central_batches`; dejar una sola relación (`centralBatch` o similar) y usarla en todo el flujo.
  Resultado esperado: DB, modelo y UI hablan del mismo lote.

- `C2. Normalizar montos`
  Archivos: [database/migrations/2025_03_24_170509_create_purchases_table.php](C:/Users/PAOLA/Herd/mce1.0.0/database/migrations/2025_03_24_170509_create_purchases_table.php:20), [app/Models/Purchase.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Models/Purchase.php:29)
  Cambios: pasar `purchases.total` a decimal y cast a `decimal:2`.
  Resultado esperado: no se pierden decimales.

- `C3. Formalizar estados y auditoría`
  Archivos: [database/migrations/2025_03_24_170509_create_purchases_table.php](C:/Users/PAOLA/Herd/mce1.0.0/database/migrations/2025_03_24_170509_create_purchases_table.php:16), [app/Filament/Resources/PurchaseResource.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/Resources/PurchaseResource.php:55), [app/Filament/TenantManager/Resources/PurchaseResource.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/TenantManager/Resources/PurchaseResource.php:63)
  Cambios: decidir si se conservarán `ready` y `dispatched`; agregar columnas reales como `confirmed_at`; quitar referencias a columnas inexistentes.
  Resultado esperado: el estado refleja el proceso real y se puede auditar.

**Iteración D: endurecer UI operativa**
- `D1. Reducir surface area de tenantManager/Purchase`
  Archivos: [app/Filament/TenantManager/Resources/PurchaseResource.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/TenantManager/Resources/PurchaseResource.php:48), [app/Filament/TenantManager/Resources/PurchaseResource/Pages/CreatePurchase.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/TenantManager/Resources/PurchaseResource/Pages/CreatePurchase.php:9), [app/Filament/TenantManager/Resources/PurchaseResource/Pages/EditPurchase.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/TenantManager/Resources/PurchaseResource/Pages/EditPurchase.php:13)
  Cambios: volver el recurso casi de solo lectura operativa; no permitir crear compras ahí; limitar edición de `team_id`, `supplier_id`, `status`, `total`.
  Resultado esperado: `tenantManager` procesa, no reescribe el pedido del cliente.

- `D2. Corregir navegación del dispatch`
  Archivos: [app/Filament/TenantManager/Resources/PurchaseResource.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/TenantManager/Resources/PurchaseResource.php:166)
  Cambios: usar `$record->dispatch?->id` en `editDispatch`.
  Resultado esperado: siempre abre el despacho correcto.

- `D3. Endurecer edición de líneas del dispatch`
  Archivos: [app/Filament/TenantManager/Resources/DispatchResource/RelationManagers/ItemsRelationManager.php](C:/Users/PAOLA/Herd/mce1.0.0/app/Filament/TenantManager/Resources/DispatchResource/RelationManagers/ItemsRelationManager.php:56)
  Cambios: quitar `ReplicateAction`; validar que la suma por `purchase_item_id` no supere lo pedido; recalcular `total` backend; bloquear edición libre de `price` si no procede.
  Resultado esperado: el despacho no puede sobredespachar ni duplicar líneas accidentalmente.

**Pruebas A Preparar**
- `T1`: crear compra, agregar items, confirmar.
- `T2`: doble submit de “Dispatch” sobre la misma compra.
- `T3`: asignar lote sin stock suficiente.
- `T4`: editar lote/cantidad de un `dispatch_item` y verificar compensación de stock.
- `T5`: intentar marcar `delivered` sin dispatch completo.
- `T6`: validar que un usuario de `admin` no vea compras de otro `team`.
- `T7`: validar que un `Super Admin` global en `tenantManager` pueda operar sin romper invariantes.

**Orden de implementación recomendado**
1. `A1-A4`
2. `B2-B4`
3. `C1-C3`
4. `D1-D3`
5. `T1-T7`

Si quieres, en el siguiente paso empiezo a ejecutar la `Iteración A` directamente en el repo.