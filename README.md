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