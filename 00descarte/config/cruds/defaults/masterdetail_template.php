<?php
/**
 * Master-Detail Configuration Template
 *
 * Use this template to configure master-detail relationships.
 * Copy this file to your entity's config directory and customize it.
 */

return [
    /*
     * Master Table Configuration
     * The parent table in the relationship (e.g., invoices, orders, quotes)
     */
    'master' => [
        'table' => 'master_table',
        'primaryKey' => 'id',
        'route' => 'master-route',        // Base route for URLs
        'title' => 'Master Records',      // Display title for list
        'singular' => 'Master Record',    // Singular form for messages

        // Field to display in dropdowns and references
        'displayField' => 'name',

        // Form fields configuration
        'fields' => [
            [
                'name' => 'id',
                'type' => 'number',
                'label' => 'ID',
                'hidden' => true,
                'readonly' => true,
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name',
                'placeholder' => 'Enter name',
                'required' => true,
                'hidden' => false,
                'readonly' => false,
                'width' => 6,  // Bootstrap column width (1-12)
                'searchable' => true,
                'orderable' => true,
            ],
            [
                'name' => 'description',
                'type' => 'textarea',
                'label' => 'Description',
                'placeholder' => 'Enter description',
                'required' => false,
                'hidden' => false,
                'readonly' => false,
                'width' => 12,
            ],
            [
                'name' => 'status',
                'type' => 'select',
                'label' => 'Status',
                'required' => true,
                'hidden' => false,
                'readonly' => false,
                'width' => 4,
                'options' => [
                    ['id' => 'draft', 'label' => 'Draft'],
                    ['id' => 'pending', 'label' => 'Pending'],
                    ['id' => 'approved', 'label' => 'Approved'],
                ],
            ],
            [
                'name' => 'date',
                'type' => 'date',
                'label' => 'Date',
                'required' => true,
                'hidden' => false,
                'readonly' => false,
                'width' => 4,
            ],
        ],
    ],

    /*
     * Detail Table Configuration
     * The child table in the relationship (e.g., invoice_items, order_items)
     */
    'detail' => [
        'table' => 'detail_table',
        'primaryKey' => 'id',
        'title' => 'Detail Items',

        // Form fields for detail rows
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
                'label' => 'Product',
                'required' => true,
                'hidden' => false,
                'options' => [
                    // Load from model or service:
                    // ['id' => 1, 'label' => 'Product A'],
                    // ['id' => 2, 'label' => 'Product B'],
                ],
            ],
            [
                'name' => 'quantity',
                'type' => 'number',
                'label' => 'Quantity',
                'required' => true,
                'hidden' => false,
                'step' => 1,
            ],
            [
                'name' => 'price',
                'type' => 'number',
                'label' => 'Price',
                'required' => true,
                'hidden' => false,
                'step' => 0.01,
            ],
            [
                'name' => 'description',
                'type' => 'text',
                'label' => 'Description',
                'required' => false,
                'hidden' => false,
            ],
        ],
    ],

    /*
     * Foreign Key Configuration
     * The field in the detail table that references the master table
     */
    'foreignKey' => 'master_id',

    /*
     * Optional: Calculated fields for totals
     */
    'totals' => [
        // 'subtotal' => ['quantity', 'price'],  // Sum of (quantity * price)
    ],
];
