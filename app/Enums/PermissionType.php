<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PermissionType: string implements HasLabel
{
    // Batch
    case VIEW_ANESTHESIA_SHEET         = 'view-anesthesia-sheet';
    case CREATE_ANESTHESIA_SHEET       = 'create-anesthesia-sheet';
    case EDIT_ANESTHESIA_SHEET         = 'edit-anesthesia-sheet';
    case DELETE_ANESTHESIA_SHEET       = 'delete-anesthesia-sheet';
    case RESTORE_ANESTHESIA_SHEET      = 'restore-anesthesia-sheet';
    case FORCE_DELETE_ANESTHESIA_SHEET = 'force-delete-anesthesia-sheet';

    // Batch
    case VIEW_BATCH         = 'view-batch';
    case CREATE_BATCH       = 'create-batch';
    case EDIT_BATCH         = 'edit-batch';
    case DELETE_BATCH       = 'delete-batch';
    case RESTORE_BATCH      = 'restore-batch';
    case FORCE_DELETE_BATCH = 'force-delete-batch';

        // Checklist
    case VIEW_CHECKLIST         = 'view-checklist';
    case CREATE_CHECKLIST       = 'create-checklist';
    case EDIT_CHECKLIST         = 'edit-checklist';
    case DELETE_CHECKLIST       = 'delete-checklist';
    case RESTORE_CHECKLIST      = 'restore-checklist';
    case FORCE_DELETE_CHECKLIST = 'force-delete-checklist';

        // ChecklistItem
    case VIEW_CHECKLIST_ITEM         = 'view-checklist-item';
    case CREATE_CHECKLIST_ITEM       = 'create-checklist-item';
    case EDIT_CHECKLIST_ITEM         = 'edit-checklist-item';
    case DELETE_CHECKLIST_ITEM       = 'delete-checklist-item';
    case RESTORE_CHECKLIST_ITEM      = 'restore-checklist-item';
    case FORCE_DELETE_CHECKLIST_ITEM = 'force-delete-checklist-item';

    // Batch
    case VIEW_CUSTOMER         = 'view-customer';
    case CREATE_CUSTOMER       = 'create-customer';
    case EDIT_CUSTOMER         = 'edit-customer';
    case DELETE_CUSTOMER       = 'delete-customer';
    case RESTORE_CUSTOMER      = 'restore-customer';
    case FORCE_DELETE_CUSTOMER = 'force-delete-customer';

        // Dispatch
    case VIEW_DISPATCH         = 'view-dispatch';
    case CREATE_DISPATCH       = 'create-dispatch';
    case EDIT_DISPATCH         = 'edit-dispatch';
    case DELETE_DISPATCH       = 'delete-dispatch';
    case RESTORE_DISPATCH      = 'restore-dispatch';
    case FORCE_DELETE_DISPATCH = 'force-delete-dispatch';

        // Document
    case VIEW_DOCUMENT         = 'view-document';
    case CREATE_DOCUMENT       = 'create-document';
    case EDIT_DOCUMENT         = 'edit-document';
    case DELETE_DOCUMENT       = 'delete-document';
    case RESTORE_DOCUMENT      = 'restore-document';
    case FORCE_DELETE_DOCUMENT = 'force-delete-document';

        // DocumentCategory
    case VIEW_DOCUMENT_CATEGORY         = 'view-document-category';
    case CREATE_DOCUMENT_CATEGORY       = 'create-document-category';
    case EDIT_DOCUMENT_CATEGORY         = 'edit-document-category';
    case DELETE_DOCUMENT_CATEGORY       = 'delete-document-category';
    case RESTORE_DOCUMENT_CATEGORY      = 'restore-document-category';
    case FORCE_DELETE_DOCUMENT_CATEGORY = 'force-delete-document-category';

        // EnvironmentalRecord
    case VIEW_ENVIRONMENTAL_RECORD         = 'view-environmental-record';
    case CREATE_ENVIRONMENTAL_RECORD       = 'create-environmental-record';
    case EDIT_ENVIRONMENTAL_RECORD         = 'edit-environmental-record';
    case DELETE_ENVIRONMENTAL_RECORD       = 'delete-environmental-record';
    case RESTORE_ENVIRONMENTAL_RECORD      = 'restore-environmental-record';
    case FORCE_DELETE_ENVIRONMENTAL_RECORD = 'force-delete-environmental-record';

        // EvaluationRecord
    case VIEW_EVALUATION_RECORD         = 'view-evaluation-record';
    case CREATE_EVALUATION_RECORD       = 'create-evaluation-record';
    case EDIT_EVALUATION_RECORD         = 'edit-evaluation-record';
    case DELETE_EVALUATION_RECORD       = 'delete-evaluation-record';
    case RESTORE_EVALUATION_RECORD      = 'restore-evaluation-record';
    case FORCE_DELETE_EVALUATION_RECORD = 'force-delete-evaluation-record';

        // ImprovementPlan
    case VIEW_IMPROVEMENT_PLAN         = 'view-improvement-plan';
    case CREATE_IMPROVEMENT_PLAN       = 'create-improvement-plan';
    case EDIT_IMPROVEMENT_PLAN         = 'edit-improvement-plan';
    case DELETE_IMPROVEMENT_PLAN       = 'delete-improvement-plan';
    case RESTORE_IMPROVEMENT_PLAN      = 'restore-improvement-plan';
    case FORCE_DELETE_IMPROVEMENT_PLAN = 'force-delete-improvement-plan';

        // Inventory
    case VIEW_INVENTORY         = 'view-inventory';
    case CREATE_INVENTORY       = 'create-inventory';
    case EDIT_INVENTORY         = 'edit-inventory';
    case DELETE_INVENTORY       = 'delete-inventory';
    case RESTORE_INVENTORY      = 'restore-inventory';
    case FORCE_DELETE_INVENTORY = 'force-delete-inventory';

        // Invoice
    case VIEW_INVOICE         = 'view-invoice';
    case CREATE_INVOICE       = 'create-invoice';
    case EDIT_INVOICE         = 'edit-invoice';
    case DELETE_INVOICE       = 'delete-invoice';
    case RESTORE_INVOICE      = 'restore-invoice';
    case FORCE_DELETE_INVOICE = 'force-delete-invoice';

        // InvoiceItems
    case VIEW_INVOICE_ITEMS         = 'view-invoice-items';
    case CREATE_INVOICE_ITEMS       = 'create-invoice-items';
    case EDIT_INVOICE_ITEMS         = 'edit-invoice-items';
    case DELETE_INVOICE_ITEMS       = 'delete-invoice-items';
    case RESTORE_INVOICE_ITEMS      = 'restore-invoice-items';
    case FORCE_DELETE_INVOICE_ITEMS = 'force-delete-invoice-items';

        // Manufacturer
    case VIEW_MANUFACTURER         = 'view-manufacturer';
    case CREATE_MANUFACTURER       = 'create-manufacturer';
    case EDIT_MANUFACTURER         = 'edit-manufacturer';
    case DELETE_MANUFACTURER       = 'delete-manufacturer';
    case RESTORE_MANUFACTURER      = 'restore-manufacturer';
    case FORCE_DELETE_MANUFACTURER = 'force-delete-manufacturer';

        // Patient
    case VIEW_PATIENT         = 'view-patient';
    case CREATE_PATIENT       = 'create-patient';
    case EDIT_PATIENT         = 'edit-patient';
    case DELETE_PATIENT       = 'delete-patient';
    case RESTORE_PATIENT      = 'restore-patient';
    case FORCE_DELETE_PATIENT = 'force-delete-patient';

        // PharmaceuticalForm
    case VIEW_PHARMACEUTICAL_FORM         = 'view-pharmaceutical-form';
    case CREATE_PHARMACEUTICAL_FORM       = 'create-pharmaceutical-form';
    case EDIT_PHARMACEUTICAL_FORM         = 'edit-pharmaceutical-form';
    case DELETE_PHARMACEUTICAL_FORM       = 'delete-pharmaceutical-form';
    case RESTORE_PHARMACEUTICAL_FORM      = 'restore-pharmaceutical-form';
    case FORCE_DELETE_PHARMACEUTICAL_FORM = 'force-delete-pharmaceutical-form';

        // Process
    case VIEW_PROCESS         = 'view-process';
    case CREATE_PROCESS       = 'create-process';
    case EDIT_PROCESS         = 'edit-process';
    case DELETE_PROCESS       = 'delete-process';
    case RESTORE_PROCESS      = 'restore-process';
    case FORCE_DELETE_PROCESS = 'force-delete-process';

        // ProcessType
    case VIEW_PROCESS_TYPE         = 'view-process-type';
    case CREATE_PROCESS_TYPE       = 'create-process-type';
    case EDIT_PROCESS_TYPE         = 'edit-process-type';
    case DELETE_PROCESS_TYPE       = 'delete-process-type';
    case RESTORE_PROCESS_TYPE      = 'restore-process-type';
    case FORCE_DELETE_PROCESS_TYPE = 'force-delete-process-type';

        // Product
    case VIEW_PRODUCT         = 'view-product';
    case CREATE_PRODUCT       = 'create-product';
    case EDIT_PRODUCT         = 'edit-product';
    case DELETE_PRODUCT       = 'delete-product';
    case RESTORE_PRODUCT      = 'restore-product';
    case FORCE_DELETE_PRODUCT = 'force-delete-product';

        // ProductCategory
    case VIEW_PRODUCT_CATEGORY         = 'view-product-category';
    case CREATE_PRODUCT_CATEGORY       = 'create-product-category';
    case EDIT_PRODUCT_CATEGORY         = 'edit-product-category';
    case DELETE_PRODUCT_CATEGORY       = 'delete-product-category';
    case RESTORE_PRODUCT_CATEGORY      = 'restore-product-category';
    case FORCE_DELETE_PRODUCT_CATEGORY = 'force-delete-product-category';

        // ProductReception
    case VIEW_PRODUCT_RECEPTION         = 'view-product-reception';
    case CREATE_PRODUCT_RECEPTION       = 'create-product-reception';
    case EDIT_PRODUCT_RECEPTION         = 'edit-product-reception';
    case DELETE_PRODUCT_RECEPTION       = 'delete-product-reception';
    case RESTORE_PRODUCT_RECEPTION      = 'restore-product-reception';
    case FORCE_DELETE_PRODUCT_RECEPTION = 'force-delete-product-reception';

        // ProductReceptionItem
    case VIEW_PRODUCT_RECEPTION_ITEM         = 'view-product-reception-item';
    case CREATE_PRODUCT_RECEPTION_ITEM       = 'create-product-reception-item';
    case EDIT_PRODUCT_RECEPTION_ITEM         = 'edit-product-reception-item';
    case DELETE_PRODUCT_RECEPTION_ITEM       = 'delete-product-reception-item';
    case RESTORE_PRODUCT_RECEPTION_ITEM      = 'restore-product-reception-item';
    case FORCE_DELETE_PRODUCT_RECEPTION_ITEM = 'force-delete-product-reception-item';

        // Purchase
    case VIEW_PURCHASE         = 'view-purchase';
    case CREATE_PURCHASE       = 'create-purchase';
    case EDIT_PURCHASE         = 'edit-purchase';
    case DELETE_PURCHASE       = 'delete-purchase';
    case RESTORE_PURCHASE      = 'restore-purchase';
    case FORCE_DELETE_PURCHASE = 'force-delete-purchase';
    case CONFIRM_PURCHASE      = 'confirm-purchase';

        // PurchaseItem
    case VIEW_PURCHASE_ITEM         = 'view-purchase-item';
    case CREATE_PURCHASE_ITEM       = 'create-purchase-item';
    case EDIT_PURCHASE_ITEM         = 'edit-purchase-item';
    case DELETE_PURCHASE_ITEM       = 'delete-purchase-item';
    case RESTORE_PURCHASE_ITEM      = 'restore-purchase-item';
    case FORCE_DELETE_PURCHASE_ITEM = 'force-delete-purchase-item';

        // Question
    case VIEW_QUESTION         = 'view-question';
    case CREATE_QUESTION       = 'create-question';
    case EDIT_QUESTION         = 'edit-question';
    case DELETE_QUESTION       = 'delete-question';
    case RESTORE_QUESTION      = 'restore-question';
    case FORCE_DELETE_QUESTION = 'force-delete-question';

        // QuestionOption
    case VIEW_QUESTION_OPTION         = 'view-question-option';
    case CREATE_QUESTION_OPTION       = 'create-question-option';
    case EDIT_QUESTION_OPTION         = 'edit-question-option';
    case DELETE_QUESTION_OPTION       = 'delete-question-option';
    case RESTORE_QUESTION_OPTION      = 'restore-question-option';
    case FORCE_DELETE_QUESTION_OPTION = 'force-delete-question-option';

        // Recipebook
    case VIEW_RECIPEBOOK         = 'view-recipebook';
    case CREATE_RECIPEBOOK       = 'create-recipebook';
    case EDIT_RECIPEBOOK         = 'edit-recipebook';
    case DELETE_RECIPEBOOK       = 'delete-recipebook';
    case RESTORE_RECIPEBOOK      = 'restore-recipebook';
    case FORCE_DELETE_RECIPEBOOK = 'force-delete-recipebook';

        // RecipebookItem
    case VIEW_RECIPEBOOK_ITEM         = 'view-recipebook-item';
    case CREATE_RECIPEBOOK_ITEM       = 'create-recipebook-item';
    case EDIT_RECIPEBOOK_ITEM         = 'edit-recipebook-item';
    case DELETE_RECIPEBOOK_ITEM       = 'delete-recipebook-item';
    case RESTORE_RECIPEBOOK_ITEM      = 'restore-recipebook-item';
    case FORCE_DELETE_RECIPEBOOK_ITEM = 'force-delete-recipebook-item';

        // Record
    case VIEW_RECORD         = 'view-record';
    case CREATE_RECORD       = 'create-record';
    case EDIT_RECORD         = 'edit-record';
    case DELETE_RECORD       = 'delete-record';
    case RESTORE_RECORD      = 'restore-record';
    case FORCE_DELETE_RECORD = 'force-delete-record';

        // Sale
    case VIEW_SALE         = 'view-sale';
    case CREATE_SALE       = 'create-sale';
    case EDIT_SALE         = 'edit-sale';
    case DELETE_SALE       = 'delete-sale';
    case RESTORE_SALE      = 'restore-sale';
    case FORCE_DELETE_SALE = 'force-delete-sale';
    case CONFIRM_SALE      = 'confirm-sale';
    case CANCEL_SALE       = 'cancel-sale';

        // SaleItem
    case VIEW_SALE_ITEM         = 'view-sale-item';
    case CREATE_SALE_ITEM       = 'create-sale-item';
    case EDIT_SALE_ITEM         = 'edit-sale-item';
    case DELETE_SALE_ITEM       = 'delete-sale-item';
    case RESTORE_SALE_ITEM      = 'restore-sale-item';
    case FORCE_DELETE_SALE_ITEM = 'force-delete-sale-item';

        // SanitaryRegistry
    case VIEW_SANITARY_REGISTRY         = 'view-sanitary-registry';
    case CREATE_SANITARY_REGISTRY       = 'create-sanitary-registry';
    case EDIT_SANITARY_REGISTRY         = 'edit-sanitary-registry';
    case DELETE_SANITARY_REGISTRY       = 'delete-sanitary-registry';
    case RESTORE_SANITARY_REGISTRY      = 'restore-sanitary-registry';
    case FORCE_DELETE_SANITARY_REGISTRY = 'force-delete-sanitary-registry';

        // Supplier
    case VIEW_SUPPLIER         = 'view-supplier';
    case CREATE_SUPPLIER       = 'create-supplier';
    case EDIT_SUPPLIER         = 'edit-supplier';
    case DELETE_SUPPLIER       = 'delete-supplier';
    case RESTORE_SUPPLIER      = 'restore-supplier';
    case FORCE_DELETE_SUPPLIER = 'force-delete-supplier';

        // Task
    case VIEW_TASK         = 'view-task';
    case CREATE_TASK       = 'create-task';
    case EDIT_TASK         = 'edit-task';
    case DELETE_TASK       = 'delete-task';
    case RESTORE_TASK      = 'restore-task';
    case FORCE_DELETE_TASK = 'force-delete-task';

        // Team
    case VIEW_TEAM         = 'view-team';
    case CREATE_TEAM       = 'create-team';
    case EDIT_TEAM         = 'edit-team';
    case DELETE_TEAM       = 'delete-team';
    case RESTORE_TEAM      = 'restore-team';
    case FORCE_DELETE_TEAM = 'force-delete-team';

        // TeamProductPrice
    case VIEW_TEAM_PRODUCT_PRICE         = 'view-team-product-price';
    case CREATE_TEAM_PRODUCT_PRICE       = 'create-team-product-price';
    case EDIT_TEAM_PRODUCT_PRICE         = 'edit-team-product-price';
    case DELETE_TEAM_PRODUCT_PRICE       = 'delete-team-product-price';
    case RESTORE_TEAM_PRODUCT_PRICE      = 'restore-team-product-price';
    case FORCE_DELETE_TEAM_PRODUCT_PRICE = 'force-delete-team-product-price';

        // Training
    case VIEW_TRAINING         = 'view-training';
    case CREATE_TRAINING       = 'create-training';
    case EDIT_TRAINING         = 'edit-training';
    case DELETE_TRAINING       = 'delete-training';
    case RESTORE_TRAINING      = 'restore-training';
    case FORCE_DELETE_TRAINING = 'force-delete-training';

        // TrainingCategory
    case VIEW_TRAINING_CATEGORY         = 'view-training-category';
    case CREATE_TRAINING_CATEGORY       = 'create-training-category';
    case EDIT_TRAINING_CATEGORY         = 'edit-training-category';
    case DELETE_TRAINING_CATEGORY       = 'delete-training-category';
    case RESTORE_TRAINING_CATEGORY      = 'restore-training-category';
    case FORCE_DELETE_TRAINING_CATEGORY = 'force-delete-training-category';

        // User
    case VIEW_USER         = 'view-user';
    case CREATE_USER       = 'create-user';
    case EDIT_USER         = 'edit-user';
    case DELETE_USER       = 'delete-user';
    case RESTORE_USER      = 'restore-user';
    case FORCE_DELETE_USER = 'force-delete-user';

    public function getLabel(): ?string
    {
        return match ($this) {
            
            self::VIEW_ANESTHESIA_SHEET         => 'Ver Hojas de Anestesia',
            self::CREATE_ANESTHESIA_SHEET       => 'Crear Hojas de Anestesia',
            self::EDIT_ANESTHESIA_SHEET         => 'Editar Hojas de Anestesia',
            self::DELETE_ANESTHESIA_SHEET       => 'Eliminar Hojas de Anestesia',
            self::RESTORE_ANESTHESIA_SHEET      => 'Restarurar Hojas de Anestesia',
            self::FORCE_DELETE_ANESTHESIA_SHEET => 'Forzar la eliminación de Hojas de Anestesia',

            self::VIEW_BATCH         => 'Ver Batches',
            self::CREATE_BATCH       => 'Crear Batches',
            self::EDIT_BATCH         => 'Editar Batches',
            self::DELETE_BATCH       => 'Eliminar Batches',
            self::RESTORE_BATCH      => 'Restarurar Batches',
            self::FORCE_DELETE_BATCH => 'Forzar la eliminación de Batches',
            // Checklist
            self::VIEW_CHECKLIST         => 'Ver Checklists',
            self::CREATE_CHECKLIST       => 'Crear Checklists',
            self::EDIT_CHECKLIST         => 'Editar Checklists',
            self::DELETE_CHECKLIST       => 'Eliminar Checklists',
            self::RESTORE_CHECKLIST      => 'Restaurar Checklists',
            self::FORCE_DELETE_CHECKLIST => 'Forzar la eliminación de Checklists',

            // ChecklistItem
            self::VIEW_CHECKLIST_ITEM         => 'Ver Checklist Items',
            self::CREATE_CHECKLIST_ITEM       => 'Crear Checklist Items',
            self::EDIT_CHECKLIST_ITEM         => 'Editar Checklist Items',
            self::DELETE_CHECKLIST_ITEM       => 'Eliminar Checklist Items',
            self::RESTORE_CHECKLIST_ITEM      => 'Restaurar Checklist Items',
            self::FORCE_DELETE_CHECKLIST_ITEM => 'Forzar la eliminación de Checklist Items',

            
            self::VIEW_CUSTOMER         => 'Ver Clientes',
            self::CREATE_CUSTOMER       => 'Crear Clientes',
            self::EDIT_CUSTOMER         => 'Editar Clientes',
            self::DELETE_CUSTOMER       => 'Eliminar Clientes',
            self::RESTORE_CUSTOMER      => 'Restarurar Clientes',
            self::FORCE_DELETE_CUSTOMER => 'Forzar la eliminación de Clientes',

            // Dispatch
            self::VIEW_DISPATCH         => 'Ver Despachos',
            self::CREATE_DISPATCH       => 'Crear Despachos',
            self::EDIT_DISPATCH         => 'Editar Despachos',
            self::DELETE_DISPATCH       => 'Eliminar Despachos',
            self::RESTORE_DISPATCH      => 'Restaurar Despachos',
            self::FORCE_DELETE_DISPATCH => 'Forzar la eliminación de Despachos',

            // Document
            self::VIEW_DOCUMENT         => 'Ver Documentos',
            self::CREATE_DOCUMENT       => 'Crear Documentos',
            self::EDIT_DOCUMENT         => 'Editar Documentos',
            self::DELETE_DOCUMENT       => 'Eliminar Documentos',
            self::RESTORE_DOCUMENT      => 'Restaurar Documentos',
            self::FORCE_DELETE_DOCUMENT => 'Forzar la eliminación de Documentos',

            // DocumentCategory
            self::VIEW_DOCUMENT_CATEGORY         => 'Ver Categorías de Documento',
            self::CREATE_DOCUMENT_CATEGORY       => 'Crear Categorías de Documento',
            self::EDIT_DOCUMENT_CATEGORY         => 'Editar Categorías de Documento',
            self::DELETE_DOCUMENT_CATEGORY       => 'Eliminar Categorías de Documento',
            self::RESTORE_DOCUMENT_CATEGORY      => 'Restaurar Categorías de Documento',
            self::FORCE_DELETE_DOCUMENT_CATEGORY => 'Forzar la eliminación de Categorías de Documento',

            // EnvironmentalRecord
            self::VIEW_ENVIRONMENTAL_RECORD         => 'Ver Registros Ambientales',
            self::CREATE_ENVIRONMENTAL_RECORD       => 'Crear Registros Ambientales',
            self::EDIT_ENVIRONMENTAL_RECORD         => 'Editar Registros Ambientales',
            self::DELETE_ENVIRONMENTAL_RECORD       => 'Eliminar Registros Ambientales',
            self::RESTORE_ENVIRONMENTAL_RECORD      => 'Restaurar Registros Ambientales',
            self::FORCE_DELETE_ENVIRONMENTAL_RECORD => 'Forzar la eliminación de Registros Ambientales',

            // EvaluationRecord
            self::VIEW_EVALUATION_RECORD         => 'Ver Registros de Evaluación',
            self::CREATE_EVALUATION_RECORD       => 'Crear Registros de Evaluación',
            self::EDIT_EVALUATION_RECORD         => 'Editar Registros de Evaluación',
            self::DELETE_EVALUATION_RECORD       => 'Eliminar Registros de Evaluación',
            self::RESTORE_EVALUATION_RECORD      => 'Restaurar Registros de Evaluación',
            self::FORCE_DELETE_EVALUATION_RECORD => 'Forzar la eliminación de Registros de Evaluación',

            // ImprovementPlan
            self::VIEW_IMPROVEMENT_PLAN         => 'Ver Planes de Mejora',
            self::CREATE_IMPROVEMENT_PLAN       => 'Crear Planes de Mejora',
            self::EDIT_IMPROVEMENT_PLAN         => 'Editar Planes de Mejora',
            self::DELETE_IMPROVEMENT_PLAN       => 'Eliminar Planes de Mejora',
            self::RESTORE_IMPROVEMENT_PLAN      => 'Restaurar Planes de Mejora',
            self::FORCE_DELETE_IMPROVEMENT_PLAN => 'Forzar la eliminación de Planes de Mejora',

            // Inventory
            self::VIEW_INVENTORY         => 'Ver Inventarios',
            self::CREATE_INVENTORY       => 'Crear Inventarios',
            self::EDIT_INVENTORY         => 'Editar Inventarios',
            self::DELETE_INVENTORY       => 'Eliminar Inventarios',
            self::RESTORE_INVENTORY      => 'Restaurar Inventarios',
            self::FORCE_DELETE_INVENTORY => 'Forzar la eliminación de Inventarios',

            // Invoice
            self::VIEW_INVOICE         => 'Ver Facturas',
            self::CREATE_INVOICE       => 'Crear Facturas',
            self::EDIT_INVOICE         => 'Editar Facturas',
            self::DELETE_INVOICE       => 'Eliminar Facturas',
            self::RESTORE_INVOICE      => 'Restaurar Facturas',
            self::FORCE_DELETE_INVOICE => 'Forzar la eliminación de Facturas',

            // InvoiceItems
            self::VIEW_INVOICE_ITEMS         => 'Ver ítems de Facturas',
            self::CREATE_INVOICE_ITEMS       => 'Crear ítems de Facturas',
            self::EDIT_INVOICE_ITEMS         => 'Editar ítems de Facturas',
            self::DELETE_INVOICE_ITEMS       => 'Eliminar ítems de Facturas',
            self::RESTORE_INVOICE_ITEMS      => 'Restaurar ítems de Facturas',
            self::FORCE_DELETE_INVOICE_ITEMS => 'Forzar la eliminación de ítems de Facturas',

            // Manufacturer
            self::VIEW_MANUFACTURER         => 'Ver Fabricantes',
            self::CREATE_MANUFACTURER       => 'Crear Fabricantes',
            self::EDIT_MANUFACTURER         => 'Editar Fabricantes',
            self::DELETE_MANUFACTURER       => 'Eliminar Fabricantes',
            self::RESTORE_MANUFACTURER      => 'Restaurar Fabricantes',
            self::FORCE_DELETE_MANUFACTURER => 'Forzar la eliminación de Fabricantes',

            // Patient
            self::VIEW_PATIENT         => 'Ver Pacientes',
            self::CREATE_PATIENT       => 'Crear Pacientes',
            self::EDIT_PATIENT         => 'Editar Pacientes',
            self::DELETE_PATIENT       => 'Eliminar Pacientes',
            self::RESTORE_PATIENT      => 'Restaurar Pacientes',
            self::FORCE_DELETE_PATIENT => 'Forzar la eliminación de Pacientes',

            // PharmaceuticalForm
            self::VIEW_PHARMACEUTICAL_FORM         => 'Ver Formas Farmacéuticas',
            self::CREATE_PHARMACEUTICAL_FORM       => 'Crear Formas Farmacéuticas',
            self::EDIT_PHARMACEUTICAL_FORM         => 'Editar Formas Farmacéuticas',
            self::DELETE_PHARMACEUTICAL_FORM       => 'Eliminar Formas Farmacéuticas',
            self::RESTORE_PHARMACEUTICAL_FORM      => 'Restaurar Formas Farmacéuticas',
            self::FORCE_DELETE_PHARMACEUTICAL_FORM => 'Forzar la eliminación de Formas Farmacéuticas',

            // Process
            self::VIEW_PROCESS         => 'Ver Procesos',
            self::CREATE_PROCESS       => 'Crear Procesos',
            self::EDIT_PROCESS         => 'Editar Procesos',
            self::DELETE_PROCESS       => 'Eliminar Procesos',
            self::RESTORE_PROCESS      => 'Restaurar Procesos',
            self::FORCE_DELETE_PROCESS => 'Forzar la eliminación de Procesos',

            // ProcessType
            self::VIEW_PROCESS_TYPE         => 'Ver Tipos de Proceso',
            self::CREATE_PROCESS_TYPE       => 'Crear Tipos de Proceso',
            self::EDIT_PROCESS_TYPE         => 'Editar Tipos de Proceso',
            self::DELETE_PROCESS_TYPE       => 'Eliminar Tipos de Proceso',
            self::RESTORE_PROCESS_TYPE      => 'Restaurar Tipos de Proceso',
            self::FORCE_DELETE_PROCESS_TYPE => 'Forzar la eliminación de Tipos de Proceso',

            // Product
            self::VIEW_PRODUCT         => 'Ver Productos',
            self::CREATE_PRODUCT       => 'Crear Productos',
            self::EDIT_PRODUCT         => 'Editar Productos',
            self::DELETE_PRODUCT       => 'Eliminar Productos',
            self::RESTORE_PRODUCT      => 'Restaurar Productos',
            self::FORCE_DELETE_PRODUCT => 'Forzar la eliminación de Productos',

            // ProductCategory
            self::VIEW_PRODUCT_CATEGORY         => 'Ver Categorías de Producto',
            self::CREATE_PRODUCT_CATEGORY       => 'Crear Categorías de Producto',
            self::EDIT_PRODUCT_CATEGORY         => 'Editar Categorías de Producto',
            self::DELETE_PRODUCT_CATEGORY       => 'Eliminar Categorías de Producto',
            self::RESTORE_PRODUCT_CATEGORY      => 'Restaurar Categorías de Producto',
            self::FORCE_DELETE_PRODUCT_CATEGORY => 'Forzar la eliminación de Categorías de Producto',

            // ProductReception
            self::VIEW_PRODUCT_RECEPTION         => 'Ver Recepciones de Producto',
            self::CREATE_PRODUCT_RECEPTION       => 'Crear Recepciones de Producto',
            self::EDIT_PRODUCT_RECEPTION         => 'Editar Recepciones de Producto',
            self::DELETE_PRODUCT_RECEPTION       => 'Eliminar Recepciones de Producto',
            self::RESTORE_PRODUCT_RECEPTION      => 'Restaurar Recepciones de Producto',
            self::FORCE_DELETE_PRODUCT_RECEPTION => 'Forzar la eliminación de Recepciones de Producto',

            // ProductReceptionItem
            self::VIEW_PRODUCT_RECEPTION_ITEM         => 'Ver Ítems de Recepción de Producto',
            self::CREATE_PRODUCT_RECEPTION_ITEM       => 'Crear Ítems de Recepción de Producto',
            self::EDIT_PRODUCT_RECEPTION_ITEM         => 'Editar Ítems de Recepción de Producto',
            self::DELETE_PRODUCT_RECEPTION_ITEM       => 'Eliminar Ítems de Recepción de Producto',
            self::RESTORE_PRODUCT_RECEPTION_ITEM      => 'Restaurar Ítems de Recepción de Producto',
            self::FORCE_DELETE_PRODUCT_RECEPTION_ITEM => 'Forzar la eliminación de Ítems de Recepción de Producto',

            // Purchase
            self::VIEW_PURCHASE         => 'Ver Compras',
            self::CREATE_PURCHASE       => 'Crear Compras',
            self::EDIT_PURCHASE         => 'Editar Compras',
            self::DELETE_PURCHASE       => 'Eliminar Compras',
            self::RESTORE_PURCHASE      => 'Restaurar Compras',
            self::FORCE_DELETE_PURCHASE => 'Forzar la eliminación de Compras',
            self::CONFIRM_PURCHASE      => 'Confirmar Órdenes de Compra',

            // PurchaseItem
            self::VIEW_PURCHASE_ITEM         => 'Ver Ítems de Compra',
            self::CREATE_PURCHASE_ITEM       => 'Crear Ítems de Compra',
            self::EDIT_PURCHASE_ITEM         => 'Editar Ítems de Compra',
            self::DELETE_PURCHASE_ITEM       => 'Eliminar Ítems de Compra',
            self::RESTORE_PURCHASE_ITEM      => 'Restaurar Ítems de Compra',
            self::FORCE_DELETE_PURCHASE_ITEM => 'Forzar la eliminación de Ítems de Compra',

            // Question
            self::VIEW_QUESTION         => 'Ver Preguntas',
            self::CREATE_QUESTION       => 'Crear Preguntas',
            self::EDIT_QUESTION         => 'Editar Preguntas',
            self::DELETE_QUESTION       => 'Eliminar Preguntas',
            self::RESTORE_QUESTION      => 'Restaurar Preguntas',
            self::FORCE_DELETE_QUESTION => 'Forzar la eliminación de Preguntas',

            // QuestionOption
            self::VIEW_QUESTION_OPTION         => 'Ver Opciones de Pregunta',
            self::CREATE_QUESTION_OPTION       => 'Crear Opciones de Pregunta',
            self::EDIT_QUESTION_OPTION         => 'Editar Opciones de Pregunta',
            self::DELETE_QUESTION_OPTION       => 'Eliminar Opciones de Pregunta',
            self::RESTORE_QUESTION_OPTION      => 'Restaurar Opciones de Pregunta',
            self::FORCE_DELETE_QUESTION_OPTION => 'Forzar la eliminación de Opciones de Pregunta',

            // Recipebook
            self::VIEW_RECIPEBOOK         => 'Ver Recetarios',
            self::CREATE_RECIPEBOOK       => 'Crear Recetarios',
            self::EDIT_RECIPEBOOK         => 'Editar Recetarios',
            self::DELETE_RECIPEBOOK       => 'Eliminar Recetarios',
            self::RESTORE_RECIPEBOOK      => 'Restaurar Recetarios',
            self::FORCE_DELETE_RECIPEBOOK => 'Forzar la eliminación de Recetarios',

            // RecipebookItem
            self::VIEW_RECIPEBOOK_ITEM         => 'Ver Ítems de Recetario',
            self::CREATE_RECIPEBOOK_ITEM       => 'Crear Ítems de Recetario',
            self::EDIT_RECIPEBOOK_ITEM         => 'Editar Ítems de Recetario',
            self::DELETE_RECIPEBOOK_ITEM       => 'Eliminar Ítems de Recetario',
            self::RESTORE_RECIPEBOOK_ITEM      => 'Restaurar Ítems de Recetario',
            self::FORCE_DELETE_RECIPEBOOK_ITEM => 'Forzar la eliminación de Ítems de Recetario',

            // Record
            self::VIEW_RECORD         => 'Ver Registros',
            self::CREATE_RECORD       => 'Crear Registros',
            self::EDIT_RECORD         => 'Editar Registros',
            self::DELETE_RECORD       => 'Eliminar Registros',
            self::RESTORE_RECORD      => 'Restaurar Registros',
            self::FORCE_DELETE_RECORD => 'Forzar la eliminación de Registros',

            // Sale
            self::VIEW_SALE         => 'Ver Ventas',
            self::CREATE_SALE       => 'Crear Ventas',
            self::EDIT_SALE         => 'Editar Ventas',
            self::DELETE_SALE       => 'Eliminar Ventas',
            self::RESTORE_SALE      => 'Restaurar Ventas',
            self::FORCE_DELETE_SALE => 'Forzar la eliminación de Ventas',
            self::CONFIRM_SALE      => 'Confirmar Ventas y facturar',
            self::CANCEL_SALE       => 'Cancelar Ventas',

            // SaleItem
            self::VIEW_SALE_ITEM         => 'Ver Ítems de Venta',
            self::CREATE_SALE_ITEM       => 'Crear Ítems de Venta',
            self::EDIT_SALE_ITEM         => 'Editar Ítems de Venta',
            self::DELETE_SALE_ITEM       => 'Eliminar Ítems de Venta',
            self::RESTORE_SALE_ITEM      => 'Restaurar Ítems de Venta',
            self::FORCE_DELETE_SALE_ITEM => 'Forzar la eliminación de Ítems de Venta',

            // SanitaryRegistry
            self::VIEW_SANITARY_REGISTRY         => 'Ver Registros Sanitarios',
            self::CREATE_SANITARY_REGISTRY       => 'Crear Registros Sanitarios',
            self::EDIT_SANITARY_REGISTRY         => 'Editar Registros Sanitarios',
            self::DELETE_SANITARY_REGISTRY       => 'Eliminar Registros Sanitarios',
            self::RESTORE_SANITARY_REGISTRY      => 'Restaurar Registros Sanitarios',
            self::FORCE_DELETE_SANITARY_REGISTRY => 'Forzar la eliminación de Registros Sanitarios',

            // Supplier
            self::VIEW_SUPPLIER         => 'Ver Proveedores',
            self::CREATE_SUPPLIER       => 'Crear Proveedores',
            self::EDIT_SUPPLIER         => 'Editar Proveedores',
            self::DELETE_SUPPLIER       => 'Eliminar Proveedores',
            self::RESTORE_SUPPLIER      => 'Restaurar Proveedores',
            self::FORCE_DELETE_SUPPLIER => 'Forzar la eliminación de Proveedores',

            // Task
            self::VIEW_TASK         => 'Ver Tareas',
            self::CREATE_TASK       => 'Crear Tareas',
            self::EDIT_TASK         => 'Editar Tareas',
            self::DELETE_TASK       => 'Eliminar Tareas',
            self::RESTORE_TASK      => 'Restaurar Tareas',
            self::FORCE_DELETE_TASK => 'Forzar la eliminación de Tareas',

            // Team
            self::VIEW_TEAM         => 'Ver Equipos',
            self::CREATE_TEAM       => 'Crear Equipos',
            self::EDIT_TEAM         => 'Editar Equipos',
            self::DELETE_TEAM       => 'Eliminar Equipos',
            self::RESTORE_TEAM      => 'Restaurar Equipos',
            self::FORCE_DELETE_TEAM => 'Forzar la eliminación de Equipos',

            // TeamProductPrice
            self::VIEW_TEAM_PRODUCT_PRICE         => 'Ver Precios de Producto del Equipo',
            self::CREATE_TEAM_PRODUCT_PRICE       => 'Crear Precios de Producto del Equipo',
            self::EDIT_TEAM_PRODUCT_PRICE         => 'Editar Precios de Producto del Equipo',
            self::DELETE_TEAM_PRODUCT_PRICE       => 'Eliminar Precios de Producto del Equipo',
            self::RESTORE_TEAM_PRODUCT_PRICE      => 'Restaurar Precios de Producto del Equipo',
            self::FORCE_DELETE_TEAM_PRODUCT_PRICE => 'Forzar la eliminación de Precios de Producto del Equipo',

            // Training
            self::VIEW_TRAINING         => 'Ver Capacitaciones',
            self::CREATE_TRAINING       => 'Crear Capacitaciones',
            self::EDIT_TRAINING         => 'Editar Capacitaciones',
            self::DELETE_TRAINING       => 'Eliminar Capacitaciones',
            self::RESTORE_TRAINING      => 'Restaurar Capacitaciones',
            self::FORCE_DELETE_TRAINING => 'Forzar la eliminación de Capacitaciones',

            // TrainingCategory
            self::VIEW_TRAINING_CATEGORY         => 'Ver Categorías de Capacitación',
            self::CREATE_TRAINING_CATEGORY       => 'Crear Categorías de Capacitación',
            self::EDIT_TRAINING_CATEGORY         => 'Editar Categorías de Capacitación',
            self::DELETE_TRAINING_CATEGORY       => 'Eliminar Categorías de Capacitación',
            self::RESTORE_TRAINING_CATEGORY      => 'Restaurar Categorías de Capacitación',
            self::FORCE_DELETE_TRAINING_CATEGORY => 'Forzar la eliminación de Categorías de Capacitación',

            // User
            self::VIEW_USER         => 'Ver Usuarios',
            self::CREATE_USER       => 'Crear Usuarios',
            self::EDIT_USER         => 'Editar Usuarios',
            self::DELETE_USER       => 'Eliminar Usuarios',
            self::RESTORE_USER      => 'Restaurar Usuarios',
            self::FORCE_DELETE_USER => 'Forzar la eliminación de Usuarios',
        };
    }
}
