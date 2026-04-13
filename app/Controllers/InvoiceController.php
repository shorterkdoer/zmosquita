<?php

namespace App\Controllers;

use Foundation\Crud\MasterDetailController;
use Foundation\Core\Request;
use App\Models\Product;

/**
 * Invoice Controller - Master-Detail Example
 *
 * Demonstrates how to implement a master-detail relationship
 * where an Invoice (master) has multiple Invoice Items (detail).
 */
class InvoiceController extends MasterDetailController
{
    /**
     * Master table configuration.
     */
    protected array $masterConfig = [
        'table' => 'invoices',
        'primaryKey' => 'id',
        'route' => 'invoices',
        'title' => 'Facturas',
        'singular' => 'Factura',
        'displayField' => 'number',

        'fields' => [
            [
                'name' => 'id',
                'type' => 'number',
                'label' => 'ID',
                'hidden' => true,
                'readonly' => true,
            ],
            [
                'name' => 'number',
                'type' => 'text',
                'label' => 'Número',
                'placeholder' => 'FAC-YYYY-NNNN',
                'required' => true,
                'hidden' => false,
                'readonly' => false,
                'width' => 4,
                'searchable' => true,
                'orderable' => true,
            ],
            [
                'name' => 'customer_name',
                'type' => 'text',
                'label' => 'Cliente',
                'placeholder' => 'Nombre del cliente',
                'required' => true,
                'hidden' => false,
                'readonly' => false,
                'width' => 6,
                'searchable' => true,
                'orderable' => true,
            ],
            [
                'name' => 'invoice_date',
                'type' => 'date',
                'label' => 'Fecha',
                'required' => true,
                'hidden' => false,
                'readonly' => false,
                'width' => 4,
                'searchable' => true,
                'orderable' => true,
            ],
            [
                'name' => 'due_date',
                'type' => 'date',
                'label' => 'Vencimiento',
                'required' => false,
                'hidden' => false,
                'readonly' => false,
                'width' => 4,
                'searchable' => true,
                'orderable' => true,
            ],
            [
                'name' => 'status',
                'type' => 'select',
                'label' => 'Estado',
                'required' => true,
                'hidden' => false,
                'readonly' => false,
                'width' => 4,
                'searchable' => true,
                'orderable' => true,
                'options' => [
                    ['id' => 'draft', 'label' => 'Borrador'],
                    ['id' => 'sent', 'label' => 'Enviada'],
                    ['id' => 'paid', 'label' => 'Pagada'],
                    ['id' => 'cancelled', 'label' => 'Cancelada'],
                ],
            ],
            [
                'name' => 'notes',
                'type' => 'textarea',
                'label' => 'Notas',
                'placeholder' => 'Notas adicionales',
                'required' => false,
                'hidden' => false,
                'readonly' => false,
                'width' => 12,
            ],
        ],
    ];

    /**
     * Detail table configuration.
     */
    protected array $detailConfig = [
        'table' => 'invoice_items',
        'primaryKey' => 'id',
        'title' => 'Ítems de Factura',

        'fields' => [
            [
                'name' => 'id',
                'type' => 'number',
                'label' => 'ID',
                'hidden' => true,
            ],
            [
                'name' => 'product_id',
                'type' => 'select',
                'label' => 'Producto',
                'required' => true,
                'hidden' => false,
                'options' => [], // Will be loaded dynamically
            ],
            [
                'name' => 'description',
                'type' => 'text',
                'label' => 'Descripción',
                'placeholder' => 'Descripción del ítem',
                'required' => false,
                'hidden' => false,
            ],
            [
                'name' => 'quantity',
                'type' => 'number',
                'label' => 'Cantidad',
                'required' => true,
                'hidden' => false,
                'step' => 1,
            ],
            [
                'name' => 'unit_price',
                'type' => 'number',
                'label' => 'Precio Unit.',
                'required' => true,
                'hidden' => false,
                'step' => 0.01,
            ],
            [
                'name' => 'tax_rate',
                'type' => 'number',
                'label' => 'IVA (%)',
                'required' => true,
                'hidden' => false,
                'step' => 0.01,
            ],
        ],
    ];

    /**
     * Foreign key in detail table.
     */
    protected string $foreignKey = 'invoice_id';

    /**
     * Override to load product options dynamically.
     */
    protected function getDetailConfig(): array
    {
        $config = parent::getDetailConfig();

        // Load products for dropdown
        $products = Product::all();
        $config['fields'][1]['options'] = array_map(function ($p) {
            return [
                'id' => $p['id'],
                'label' => $p['name'] . ' ($' . number_format($p['price'], 2) . ')'
            ];
        }, $products);

        return $config;
    }

    /**
     * Override to load master field options dynamically.
     */
    protected function getMasterConfig(): array
    {
        return $this->masterConfig;
    }

    /**
     * Custom action: View invoice details.
     */
    public function view(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            $this->redirect('/invoices');
        }

        $master = $this->getMasterConfig();
        $detail = $this->getDetailConfig();

        // Fetch invoice with totals
        $sql = "SELECT i.*,
                       COUNT(ii.id) as item_count,
                       SUM(ii.quantity * ii.unit_price) as subtotal,
                       SUM(ii.quantity * ii.unit_price * ii.tax_rate / 100) as tax_total,
                       SUM(ii.quantity * ii.unit_price * (1 + ii.tax_rate / 100)) as total
                FROM {$master['table']} i
                LEFT JOIN {$detail['table']} ii ON i.{$master['primaryKey']} = ii.{$this->foreignKey}
                WHERE i.{$master['primaryKey']} = ?
                GROUP BY i.{$master['primaryKey']}";

        $invoice = $this->findById($master['table'], $master['primaryKey'], $id);
        $totals = $this->fetchQuery($sql, [$id])[0] ?? [];

        $items = $this->getDetailRecords($id);

        $this->view('invoices/view', [
            'invoice' => array_merge($invoice, $totals),
            'items' => $items,
            'master' => $master,
        ]);
    }

    /**
     * Custom action: Print invoice.
     */
    public function print(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            $this->redirect('/invoices');
        }

        // Generate PDF invoice...
        // This would use your PDF library
        $this->redirect("/invoices/view/$id");
    }
}
