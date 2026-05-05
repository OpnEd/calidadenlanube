# Manual de Usuario Gestión Veterinaria

## 1. Módulos Principales

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
#### **Hojas de Anestesia:** Digitalización del consumo de MCE en quirófano.

##### Pasos para la creación de una hoja de Anestesia:

Para crear exitosamente una hoja de anestesia debemos garantizar previamente algunos datos
en la base de datos:

1. Cliente o propietario
2. Paciente
3. Productos en el inventario

    1. Para ingresar productos al inventario se debe crear primero los lotes de los productos,
y para esto se debe tener previamente fabricantes y registros sanitarios. Estos dos últimos,
son datos globales que deben ser poblados por el proveedor del SaaS.

4. Usuarios con roles de Director Técnico o médico y estar marcado como cirujano.

###### **Procedimiento**

1. ***Ingreso a las hojas de anestesia***

![Ingreso a Hojas de anestesia](/storage/app_operation/8-anesthesia_index.png)

En el menú principal, a la izquierda de la pantalla, clickear **Hojas de Anestesia**. Esta acción lo llevará al índex de hojas de anestesia, donde podrá ver detalles de cada una clickeando sobre la fila correspondiente, o clickeando el botón **Editar** que está dentro de los tres puntitos verticales al final de cada fila.

2. ***Apertura de nuevas hojas de anestesia***

![Abrir Hojas de anestesia](/storage/app_operation/8a-abrir_hoja_anestesia.png)

En la cabecera del index o lista de hojas de anestesia, a la derecha, se encuentra el botón para **crear nuevas hojas**, clickearlo.

![Asociar receta](/storage/app_operation/8b-modal_select_recipe.png)

Esta acción abre un modal en el cual deberá **escoger el consecutivo del recetario** que quiere asociar a esta hoja de anestesia; inicialmente este consecutivo de recetario tiene un *estado* de **available**, pero al ser asociado a una hoja pasa a estar en **in_use**, por lo cual ya no aparecerá en la lista de consecutivos a seleccionar cuando emerge el modal; al clickear el botón de crear, se creará la hoja de anestesia en estado **"opened"**, y será redirigido a la hoja en sí, que es un formulario en el que deberá diligenciar la información pertinente así como asociar medicamentos y sus cantidades.

3. ***Diligenciamiento de la hoja de anestesia***

    1. *Diligenciar datos generales*

    ![Diligenciar datos generales](/storage/app_operation/9_anesthesia_sheet_general_info.png)
    
    Una vez en el formulario de edición deberá **seleccionar** el propietario, el paciente, el médico que está operando, la  hora de inicio de la anestesia.

    2. *Diligenciar la anamnesis y las notas de anestesia*

    ![Anamnesis y notas de anestesia](/storage/app_operation/10_anamnesis_anestesia_notes.png)
    
    **Estos campos funcionan como una pequeña tabla** de dos columnas. En la primera columna escribes el nombre o concepto, y en la segunda el dato correspondiente. Ejm:

    |**Característica**|**Valor**|
    |:---------------|:------------|
    | Color          | Rojo        |
    | Peso           | 2 Kg        |

    3. *Adjuntar los medicamentos*

    ![Ítems de hoja de anestesia](/storage/app_operation/10_anhesthesia_sheet_items.png)

    En la sección inferior, **ítems de hoja de anesthesia**, debe asociar los medicamentos y sus cantidades, y las etapas del ciclo quirúrgico en que fueron administrados cada uno de ellos. Es decir que puede haber múltiples adiciones de un mismo producto y de hecho esto es lo recomendable, en lugar de ir sumando en un solo valor en los casos en que se hacen múltiples administraciones.

    4. *Cerrar hoja de anestesia y descontar de inventarios*
    
    Cuando se haya finalizado la cirugía se diligencia la hora de finalización, se verifica que toda la información esté correcta, y se da click en la esquina superior derecha **confirmar y cerrar hoja de anestesia**; con esta acción, de manera automática se consolidan las cantidades totales de cada medicamento administrado, y se descuentan del inventario. El estado de la hoja cambia de **"opened"** a **"closed"** y la hoja se bloquea, pudiendo ser modificada mediante adición de historial de cambios.

    5. *Cancelación de hoja de anestesia estando en estado "opened"*

    Si se da click al botón **cancelar** estando en el formulario de edición en estado **"opened"**, emerge un modal que pregunta la razón de la cancelación. Al dar click en **cancelar** se cierra el modal, el estado de la hoja pasa de **"opened"** a **"canceled"**, y el consecutivo asociado pasa de estado **"in_use"** a estado **"available"** y a estar nuevamente presente en la lista de consecutivos seleccionables para asociar a hoja de anestesia.

    6. *Cancelación de hoja de anestesia estando en estado "closed"*

    Si una hoja de anestesia está en estado **"closed"**, esto significa que las cantidades de los medicamentos que están asociados a dicha hoja fueron descontados del inventario.
    
    Para cancelar una hoja de anestesia estando esta en estado **"closed"** se debe ir al index o lista de hojas de anestesia, dando click en **Hojas de anestesia** en el menú principal a la izquierda de la pantalla. A continuación, en cada fila de hoja de anestesia, al final de cada fila, se da click en los *tres puntitos* y, a continuación, click en **cancelar**.

    Con esta acción la hoja de anestesia pasa a estado **"canceled"**, el estado del **consecutivo de recetario** asociado pasa de **"used"** a **"available"**, y los medicamentos asociados son repuestos en el inventario.
