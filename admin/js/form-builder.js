jQuery(document).ready(function($) {
    var $container = $('#grt-fb-fields-container');
    var fieldTemplate = $('#grt-fb-field-template').html();
    var optionsTemplate = $('#grt-fb-options-template').html();

    // Type labels mapping
    var typeLabels = {
        'text': 'Text',
        'textarea': 'Textarea',
        'number': 'Number',
        'email': 'Email',
        'url': 'URL',
        'password': 'Password',
        'select': 'Dropdown',
        'date': 'Date'
    };

    // Helper to generate ID
    function generateId() {
        return 'field_' + Math.random().toString(36).substr(2, 9);
    }

    // Helper to generate Input Preview HTML
    function generateInputPreview(type, placeholder, options) {
        var html = '';
        switch(type) {
            case 'textarea':
                html = '<textarea class="grt-fb-preview-input-el" disabled placeholder="' + (placeholder || '') + '"></textarea>';
                break;
            case 'select':
                html = '<select class="grt-fb-preview-input-el" disabled><option>Select Option...</option></select>';
                break;
            case 'date':
                html = '<input type="date" class="grt-fb-preview-input-el" disabled>';
                break;
            default: // text, email, number, url
                html = '<input type="' + type + '" class="grt-fb-preview-input-el" disabled placeholder="' + (placeholder || '') + '">';
        }
        return html;
    }

    // Helper to render a field
    function renderField(field) {
        var isSystem = field.is_system || false;
        var width = field.width || '100';
        var removeBtnHtml = isSystem 
            ? '<button type="button" class="grt-fb-lock-btn" title="System Field (Cannot Remove)"><span class="dashicons dashicons-lock"></span></button>' 
            : '<button type="button" class="grt-fb-remove-btn" title="Remove Field"><span class="dashicons dashicons-trash"></span></button>';

        var inputPreview = generateInputPreview(field.type, field.placeholder, field.options);
        var requiredMark = field.required ? '*' : '';

        // Handle string/boolean system flag
        var isSystemBool = (isSystem === true || isSystem === 'true');

        var html = fieldTemplate
            .replace(/{{id}}/g, field.id)
            .replace(/{{type}}/g, field.type)
            .replace(/{{label}}/g, field.label)
            .replace(/{{placeholder}}/g, field.placeholder || '')
            .replace(/{{typeLabel}}/g, typeLabels[field.type] || field.type)
            .replace(/{{requiredChecked}}/g, field.required ? 'checked' : '')
            .replace(/{{requiredDisabled}}/g, isSystemBool && field.required ? 'disabled' : '')
            .replace(/{{width}}/g, width)
            .replace(/{{isSystem}}/g, isSystem)
            .replace(/{{systemClass}}/g, isSystemBool ? 'system-field' : '')
            .replace(/{{removeBtn}}/g, removeBtnHtml)
            .replace(/{{width100}}/g, width === '100' ? 'selected' : '')
            .replace(/{{width50}}/g, width === '50' ? 'selected' : '')
            .replace(/{{width33}}/g, width === '33' ? 'selected' : '')
            .replace(/{{inputPreview}}/g, inputPreview)
            .replace(/{{requiredMark}}/g, requiredMark);

        var optionsHtml = '';
        if (field.type === 'select') {
            optionsHtml = optionsTemplate.replace(/{{options}}/g, field.options || '');
        }
        html = html.replace(/{{optionsHtml}}/g, optionsHtml);

        return $(html);
    }

    // Initialize sortable
    $container.sortable({
        items: '.grt-fb-field-item',
        handle: '.grt-fb-handle, .grt-fb-field-preview', // Allow dragging by the whole preview area
        placeholder: 'grt-fb-field-item grt-fb-placeholder',
        tolerance: 'pointer',
        forcePlaceholderSize: true,
        cancel: '.grt-fb-actions-area button, .grt-fb-field-settings', // Prevent drag when clicking buttons or settings
        start: function(e, ui) {
            // Ensure placeholder matches the dragged item's width class
            var width = ui.item.data('width');
            ui.placeholder.removeClass('grt-fb-width-100 grt-fb-width-50 grt-fb-width-33')
                         .addClass('grt-fb-width-' + width);
            ui.placeholder.css('height', ui.item.outerHeight());
        }
    });

    // Update Width on Change
    $container.on('change', '.grt-fb-input-width', function() {
        var width = $(this).val();
        var $item = $(this).closest('.grt-fb-field-item');
        
        $item.removeClass('grt-fb-width-100 grt-fb-width-50 grt-fb-width-33')
             .addClass('grt-fb-width-' + width)
             .data('width', width);
    });

    // Add Field
    $('.grt-fb-add-field').on('click', function() {
        var type = $(this).data('type');
        var newField = {
            id: generateId(),
            type: type,
            label: 'New ' + typeLabels[type] + ' Field',
            placeholder: '',
            required: false,
            options: '',
            width: '100'
        };

        var $el = renderField(newField);
        
        if ($container.find('.grt-fb-empty-state').length) {
            $container.empty();
        }

        $container.append($el);
        $el.addClass('open'); // Open settings for new field
        
        // Scroll to new field
        $('html, body').animate({
            scrollTop: $el.offset().top - 100
        }, 500);
    });

    // Toggle Settings Panel
    $container.on('click', '.grt-fb-edit-btn', function(e) {
        e.stopPropagation();
        var $item = $(this).closest('.grt-fb-field-item');
        
        // Close other open settings
        $('.grt-fb-field-item.open').not($item).removeClass('open');
        
        $item.toggleClass('open');
    });

    // Close Settings Panel
    $container.on('click', '.grt-fb-close-settings', function(e) {
        e.stopPropagation();
        var $item = $(this).closest('.grt-fb-field-item');
        $item.removeClass('open');
    });

    // Remove Field
    $container.on('click', '.grt-fb-remove-btn', function(e) {
        e.stopPropagation();
        if (confirm('Are you sure you want to remove this field?')) {
            $(this).closest('.grt-fb-field-item').remove();
            if ($container.children().length === 0) {
                $container.html('<div class="grt-fb-empty-state"><p>No fields added. Click a field type on the left to add one.</p></div>');
            }
        }
    });

    // Live Preview Updates
    // Update Label
    $container.on('input', '.grt-fb-input-label', function() {
        var val = $(this).val();
        $(this).closest('.grt-fb-field-item').find('.grt-fb-preview-label').contents().first().replaceWith(val || '(No Label) ');
    });

    // Update Placeholder
    $container.on('input', '.grt-fb-input-placeholder', function() {
        var val = $(this).val();
        $(this).closest('.grt-fb-field-item').find('.grt-fb-preview-input-el').attr('placeholder', val);
    });

    // Update Required Mark
    $container.on('change', '.grt-fb-input-required', function() {
        var isRequired = $(this).is(':checked');
        var mark = isRequired ? '*' : '';
        $(this).closest('.grt-fb-field-item').find('.grt-fb-required-mark').text(mark);
    });

    // Load Initial Data
    if (typeof grtFormStructure !== 'undefined' && Array.isArray(grtFormStructure) && grtFormStructure.length > 0) {
        $container.empty();
        grtFormStructure.forEach(function(field) {
            $container.append(renderField(field));
        });
    }

    // Save Form
    $('#grt-fb-save-btn').on('click', function() {
        var $btn = $(this);
        var $spinner = $btn.next('.spinner');
        
        var fields = [];
        $container.find('.grt-fb-field-item').each(function() {
            var $el = $(this);
            var isSystem = $el.data('system') === true || $el.data('system') === 'true'; 
            
            var field = {
                id: $el.data('id'),
                type: $el.data('type'),
                label: $el.find('.grt-fb-input-label').val(),
                placeholder: $el.find('.grt-fb-input-placeholder').val(),
                required: $el.find('.grt-fb-input-required').is(':checked'),
                width: $el.find('.grt-fb-input-width').val(),
                is_system: isSystem,
                options: $el.find('.grt-fb-input-options').val() // Only for select
            };
            fields.push(field);
        });

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');

        $.ajax({
            url: grtTicketAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'grt_save_form_builder',
                nonce: grtTicketAdmin.nonce,
                fields: JSON.stringify(fields)
            },
            success: function(response) {
                if (response.success) {
                    alert('Form structure saved successfully!');
                } else {
                    alert('Error saving form: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('Connection error');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
});
