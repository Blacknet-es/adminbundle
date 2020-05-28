# Trendix Admin Bundle

## Cómo funcionan los filtros específicos de los listados

Para utilizar los filtros específicos se puede usar la opción specific_search_fields. He aquí un ejemplo:

```php
    public function getOptions()
    {
        return [
            'permanent_filter' => 'a1.deleted = false',
            'specific_search_fields' => [
                'category.slug' => [
                    'value' => '',
                    'type' => 'text',
                    'label' => 'slug categoría'
                ],
                'id' => [
                    'value' => '',
                    'type' => 'number',
                    'label' => 'id'
                ],
                'category.id' => [
                    'value' => '',
                    'type' => 'choice',
                    'label' => 'Categoría',
                    'choices' => [
                        ['value' => '1', 'label' => 'Categoría 1'],
                        ['value' => '2', 'label' => 'test-2']
                    ]
                ]
            ]
        ];
    }
```

Como podemos ver en este ejemplo, se debe introducir como clave el campo por el que quedemos filtrar.
Este campo puede ser un campo de una asociación de la entidad, para lo cual tendremos que indicar por qué
campo de esa asociación queremos filtrar. A su vez, dentro de cada filtro podemos indicar los siguientes
campos:
* value: Valor por defecto para el filtro. Puede estar vacío.
* type: Tipo de filtro. Los valores aceptados son "text", "number" y "choice". Si se escoje "choice", 
se debe indicar también la opción "choices".
* label: Label a mostrar para el filtro de búsqueda.
* choices: Opciones a mostrar si escogemos el tipo "choice". Por cada opción se deben indicar los campos
"value" y "label".

