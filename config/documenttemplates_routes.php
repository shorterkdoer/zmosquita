<?php

Router::get('/document_templates', [DocumentTemplateController::class, 'index']);
Router::get('/document_templates/edit/{id}', [DocumentTemplateController::class, 'edit']);
Router::post('/document_templates/update/{id}', [DocumentTemplateController::class, 'update']);
Router::get('/document_templates/create', [DocumentTemplateController::class, 'create']);
Router::post('/document_templates/store', [DocumentTemplateController::class, 'store']);
Router::get('/document_templates/delete/{id}', [DocumentTemplateController::class, 'delete']);

// Extra para vinculación y cajas
Router::get('/document_templates/{id}/textboxes', [DocumentTemplateController::class, 'editTextboxes']);
Router::get('/document_templates/{id}/bindings', [DocumentTemplateController::class, 'editBindings']);
Router::get('/document_templates/generateview/{id}', [DocumentTemplateController::class, 'generateForm']);
Router::post('/document_templates/generate', [DocumentTemplateController::class, 'generate']);
