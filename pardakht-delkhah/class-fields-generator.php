<?php
defined('ABSPATH') or die('No script kiddies please!');

class cupri_fields_generator
{
    private static $instance = null;
    /**
     * @var array|array[]
     */
    public $fields = [];

    public static function get_instance($slug = '_slug', $text_domain = '_wpm')
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($slug = '_slug', $text_domain = '_wpm');
        }

        return self::$instance;
    }

    function __construct($slug = '_slug', $text_domain = '_wpm')
    {
        $this->fields =
            array(
                array(
                    'name' => __('Text', 'cupri'),
                    'type' => 'text',
                ),
                array(
                    'name' => __('Multi Line Text', 'cupri'),
                    'type' => 'multi_line_text',
                ),
                array(
                    'name' => __('Checkbox', 'cupri'),
                    'type' => 'checkbox',
                ),
                array(
                    'name' => __('Paragraph', 'cupri'),
                    'type' => 'paragraph',
                ),
                /*array(
                    'name'=>__('Radio' , 'cupri'),
                    'type'=>'radio',
                    ),*/
                array(
                    'name' => __('ComboBox', 'cupri'),
                    'type' => 'select',
                ),
            );
        $this->render_custom_fields('_cupri', 'cupri');
    }

    public function render_custom_fields($slug = '_slug', $text_domain = '_wpm')
    {

        /**
         * I know this is dirty , but keep it here
         */
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable', false, array('jquery', 'jquery-ui-core'), false, false);

        ?>
        <style type="text/css">
            .wpm_custom_fields {
                padding: 25px;
            }

            .field_settings {
                display: none;
            }

            .m_fields {
                border: 1px solid #d2d2d2;
                box-shadow: 1px 2px 3px 1px #eee;
                margin: 10px auto;
                padding: 10px;
                position: relative;
                width: 95%;
                cursor: all-scroll;
                background: #f9f9f9;
            }

            .m_fields.field_type_builtins {
                background: #f1f1f1;
                border-radius: 12px;
            }

            .m_fields > p {
                display: inline;
            }

            .field_settings label {
                display: block;
                border-right: 1px solid #eee;
                padding-right: 5px;
            }

            .field_settings label strong {
                min-width: 110px;
                display: inline-block;
                display: block;
            }

            .field_settings input {
                font-size: 10pt;
                height: 16px;
                margin: 3px 13px 3px 3px;
            }

            .t_clear {
                display: block;
                height: 1px;
                width: 100%;
                clear: both;
            }

            .wpm_del_field {
                background: #ededed none repeat scroll 0 0;
                border-radius: 60px;
                cursor: pointer;
                margin: 5px;
                padding: 2px 9px;
                position: absolute;
                right: -10px;
                top: -10px;
                transition: all ease 0.3s;
                border: 1px solid #aaa;
            }

            .wpm_del_field:hover {
                background-color: red;
                color: #fff;
            }

            .combo_add, .combo_remove {
                background: #aaa;
                color: #444;
                padding: 4px;
                transition: all ease .3s;
            }

            .combo_add:hover, .combo_remove:hover {
                background: #fff;
            }

            .m_fields_start .field_settings_wrapper {
                display: none;
            }


            .m_fields:hover {
                background: #fff;
            }

            .wpm_field_main_name {
                width: 100%;
                padding: 5px;
                color: green;
                margin-right: 10px;
                font-weight: bold;
                display: block;
                font-size: 1.3em;
                display: inline-block;
            }

            .wpm_field_main_name .id {
                font-size: .9em;
                color: #aaa;
                min-width: 30px;
                display: inline-block;
            }

            .field_type_paragraph .f_required,
            .field_type_paragraph .f_desc {
                display: none;
            }

            <?php
            $css_display_none = array();
            foreach ($this->fields  as $__field) {
                foreach ($this->fields  as $__field2) {
                    if($__field['type'] != $__field2['type'])
                        $css_display_none[]	 = '.m_fields.field_type_'.$__field['type'].' .field_'.$__field2['type'];
                }
            }
            $css_display_none = implode(',', $css_display_none);
            echo esc_html($css_display_none).'{display: none;}';
            ?>


        </style>

        <?php

        if (!empty($_POST)) {
            if ((isset($_POST['cupriFields_form_nonce']) && wp_verify_nonce($_POST['cupriFields_form_nonce'], 'cupriFields_form_nonce'))) {
                /**
                 * Save Values
                 */
                if (isset($_POST['wpm_fields'])) {
                    $wpm_fields = cupri_array_map_recursive('sanitize_text_field', $_POST['wpm_fields']);
                    update_option($slug, $wpm_fields);

                }

                /**
                 * Reset Fields
                 */
                if (isset($_GET['cupri_reset_form'])) {
                    update_option($slug, cupri_get_defaults_fields());
                }


            } else {
                echo cupri_failed_msg('مقادیر ارسالی از منبع معتبری نیستند');

            }
        }


        $fields = get_option($slug, array());
        if ((!is_array($fields)) || !$fields)
            $fields = cupri_get_defaults_fields();

//        die(var_export($fields,1));
        ?>

        <form method="post"
              action="<?php echo esc_url(admin_url('edit.php?post_type=cupri_pay&page=cupri-fields')); ?>">
            <?php
            wp_nonce_field('cupriFields_form_nonce', 'cupriFields_form_nonce');
            ?>
            <div class="wpm_custom_fields">
                <button class="wpm_add_field button-secondary"><?php _e('+Add Field', 'cupri'); ?></button>
                <div class="t_clear"></div>
                <div class="wpm_note">
                    <p>
                        <?php
                        $notes = array(
                            // 'کلیه عناوین در "نام فیلد" قرار می گیرد',
                            // 'کلیه مقادیر در "مقدار پیشفرض" قرار می گیرد',
                        );
                        foreach ($notes as $note) {
                            echo '<span class="dashicons dashicons-yes"></span> کلیه مقادیر در "مقدار پیشفرض" قرار می گیرد.<br>';
                        }
                        ?>
                    </p>
                </div>
                <div class="t_clear"></div>
                <?php
                $data_fields_counter = 0;
                foreach (array_keys($fields['name']) as $idx => $dummy) {
                    if ($idx > $data_fields_counter) $data_fields_counter = $idx;
                }
                $data_fields_counter++;


                ?>
                <div class="fields_placeholder" data-fields-counter="<?php echo $data_fields_counter ?>">
                    <?php

                    if ((!empty($fields) || is_array($fields)) && isset($fields['name'])) {
                        foreach ($fields['name'] as $i => $name) {
                            switch ($fields['type'][$i]) {
                                case 'price':
                                    ?>
                                    <div class="m_fields field_type_price field_type_builtins"><span
                                                class="wpm_field_main_name"><span
                                                    class="id">#p</span> <?php echo esc_html($fields['name']['price']); ?> </span>
                                        <div class="field_settings">
                                            <input type="hidden" name="wpm_fields[type][price]" value="price">
                                            <h3><?php _e('General', 'cupri'); ?></h3>
                                            <label class="f_name"><strong
                                                        class="ftitle"><?php _e('Field name', 'cupri'); ?></strong>
                                                <input value="<?php if (isset($fields['name']['price'])) {
                                                    echo esc_html($fields['name']['price']);
                                                } ?>" class="wpm_change_title_name" name="wpm_fields[name][price]"
                                                       type="text">
                                            </label>
                                            <label class="f_minimal_price"><strong><?php _e('Minimum price', 'cupri'); ?><?php echo '(' . esc_html(cupri_get_currency()) . ')'; ?> </strong>
                                                <input value="<?php if (isset($fields['name']['price'])) {
                                                    echo esc_html($fields['min']['price']);
                                                } ?>" name="wpm_fields[min][price]" type="text">
                                            </label>
                                            <label class="f_default_value"><strong><?php _e('Default price', 'cupri'); ?></strong>
                                                <input value="<?php if (isset($fields['name']['price'])) {
                                                    echo esc_html($fields['default']['price']);
                                                } ?>" name="wpm_fields[default][price]" type="text">
                                            </label>
                                            <label class="f_placeholder">
                                                <strong><?php _e('Placeholder', 'cupri'); ?></strong>
                                                <input value="<?php if (isset($fields['name']['price'])) {
                                                    echo esc_html($fields['text_placeholder']['price']);
                                                } ?>" name="wpm_fields[text_placeholder][price]" type="text">
                                            </label>
                                        </div>
                                    </div>
                                    <?php
                                    break;
                                case 'mobile':
                                    ?>
                                    <div class="m_fields field_type_mobile field_type_builtins"><span
                                                class="wpm_field_main_name"><span
                                                    class="id">#m</span> <?php echo esc_html($fields['name']['mobile']); ?> </span>
                                        <div class="field_settings">
                                            <input type="hidden" name="wpm_fields[type][mobile]" value="mobile">
                                            <h3><?php _e('General', 'cupri'); ?></h3>
                                            <label class="f_name"><strong><?php _e('Field name', 'cupri'); ?></strong>
                                                <input value="<?php if (isset($fields['name']['mobile'])) {
                                                    echo esc_attr($fields['name']['mobile']);
                                                } ?>" class="wpm_change_title_name" name="wpm_fields[name][mobile]"
                                                       type="text">
                                            </label>
                                            <label class="f_placeholder">
                                                <strong><?php _e('Placeholder', 'cupri'); ?></strong>
                                                <input value="<?php if (isset($fields['text_placeholder']['mobile'])) {
                                                    echo esc_attr($fields['text_placeholder']['mobile']);
                                                } ?>" name="wpm_fields[text_placeholder][mobile]" type="text">
                                            </label>
                                            <label class="f_disable">
                                                <strong><?php _e('Disable this field ?', 'cupri'); ?></strong>
                                                <input <?php if (isset($fields['disable']['mobile']) && $fields['disable']['mobile'] == 1) {
                                                    echo ' checked=checked ';
                                                } ?> name="wpm_fields[disable][mobile]" value="1" type="checkbox">
                                            </label>
                                            <label class="f_required">
                                                <strong><?php _e('Required?', 'cupri'); ?></strong>
                                                <input <?php if (isset($fields['required']['mobile']) && $fields['required']['mobile'] == 1) {
                                                    echo ' checked=checked ';
                                                } ?> name="wpm_fields[required][mobile]" value="1" type="checkbox">
                                            </label>
                                        </div>
                                    </div>
                                    <?php
                                    break;
                                case 'email':
                                    ?>
                                    <div class="m_fields field_type_email field_type_builtins"><span
                                                class="wpm_field_main_name"><span
                                                    class="id">#e</span> <?php echo esc_html($fields['name']['email']); ?> </span>
                                        <div class="field_settings">
                                            <input type="hidden" name="wpm_fields[type][email]" value="email">
                                            <h3><?php _e('General', 'cupri'); ?></h3>
                                            <label class="f_name"><strong><?php _e('Field name', 'cupri'); ?></strong>
                                                <input value="<?php if (isset($fields['name']['email'])) {
                                                    echo esc_attr($fields['name']['email']);
                                                } ?>" class="wpm_change_title_name" name="wpm_fields[name][email]"
                                                       type="text">
                                            </label>
                                            <label class="f_placeholder">
                                                <strong><?php _e('Placeholder', 'cupri'); ?></strong>
                                                <input value="<?php if (isset($fields['text_placeholder']['email'])) {
                                                    echo esc_attr($fields['text_placeholder']['email']);
                                                } ?>" name="wpm_fields[text_placeholder][email]" type="text">
                                            </label>
                                            <label class="f_disable">
                                                <strong><?php _e('Disable this field ?', 'cupri'); ?></strong>
                                                <input <?php if (isset($fields['disable']['email']) && $fields['disable']['email'] == 1) {
                                                    echo ' checked=checked ';
                                                } ?> name="wpm_fields[disable][email]" value="1" type="checkbox">
                                            </label>
                                            <label class="f_required">
                                                <strong><?php _e('Required?', 'cupri'); ?></strong>
                                                <input <?php if (isset($fields['required']['email']) && $fields['required']['email'] == 1) {
                                                    echo ' checked=checked ';
                                                } ?> name="wpm_fields[required][email]" value="1" type="checkbox">
                                            </label>
                                        </div>
                                    </div>
                                    <?php
                                    break;

                                default:
                                    echo cupri_wp_kses($this->generate_field_html($i, $fields));
                                    break;
                            }

                        }

                    }

                    ?>
                </div> <!-- /.placeholder -->
                <div class="t_clear"></div>
            </div>
            <button class="button-primary"><?php _e('Save', 'cupri'); ?></button>
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=cupri_pay&page=cupri-fields&cupri_reset_form=true')); ?>"
               class="button-secondary"
               onclick="if(!confirm('All Fields will be destructed , Are you sure?')){return false;}"><?php _e('Reset', 'cupri'); ?></a>
        </form>

        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                jQuery(".fields_placeholder").sortable({axis: "y"});
                $('.wpm_add_field').on('click', function (e) {
                    e.preventDefault();
                    var fields_counter = parseInt($('.fields_placeholder').attr('data-fields-counter'));
                    if (isNaN(fields_counter)) {
                        fields_counter = 0;
                    }
                    fields_counter = parseInt((fields_counter + 1));
                    $('.fields_placeholder').attr('data-fields-counter', fields_counter);
                    var new_element = $('<?php        $fields = get_option($slug, array());
                        $i_new = 1;
                        if (!empty($fields['name'])) {
                            $data_fields_counter = 0;
                            foreach (array_keys($fields['name']) as $idx => $dummy) {
                                if ($idx > $data_fields_counter) $data_fields_counter = $idx;
                            }
                            $i_new = $data_fields_counter + 1;
                        } echo str_replace(array("\n", "\r"), '', cupri_wp_kses($this->generate_field_html($i_new, array(), true))); ?>');
                    new_element.appendTo(".fields_placeholder");
                    new_element.closest('.m_fields').find('.field_settings').slideDown();
                    // new_element.closest('.m_fields').find('.id').text(fields_counter+1);
                    $('.fields_placeholder').attr('data-fields-counter', fields_counter);
                    // alert(fields_counter);

                    $('html, body').animate({
                        scrollTop: new_element.offset().top
                    }, 900);


                });

                $('body').on('click', '.wpm_del_field', function () {
                    if (confirm('<?php _e('Are You Sure?', 'cupri'); ?>')) {
                        $(this).closest('.m_fields').slideUp().remove();
                    }
                });

                $('body').on('change', '.f_type_select', function () {
                    var this_parent = $(this).parents('.m_fields').eq(0);
                    this_parent[0].className = this_parent[0].className.replace(/\bfield_type_.*?\b/g, '');
                    this_parent.addClass('field_type_' + $(this).val());
                    this_parent.find('.field_settings_wrapper').slideUp();
                    this_parent.find('.field_settings_wrapper.field_' + $(this).val()).slideDown();
                });

                $('body').on('click', '.combo_remove', function () {
                    if (confirm('<?php _e('Are You Sure?', 'cupri'); ?>')) {
                        $(this).closest('.cobmobox_choices_wrapper').slideUp('slow').remove();
                    }
                });

                $('body').on('click', '.combo_add', function () {
                    var elem_index = parseInt($(this).attr('data-current-id'));
                    var element_to_add = $(this).closest('.field_settings_wrapper.field_select .f_choices');
                    var new_element = $('<?php $to_add = '<div class="cobmobox_choices_wrapper">								<strong>&nbsp;</strong>								<input value="" name="wpm_fields[combobox_choices][\'+elem_index+\'][]" type="text">								<span data-current-id="\'+elem_index+\'" class="combo_add">+</span>								<span class="combo_remove">-</span>							</div>'; echo str_replace(array("\n", "\r"), '', cupri_wp_kses($to_add)); ?>');
                    new_element.appendTo(element_to_add);
                });

                $('body').on('click', '.wpm_field_main_name', function () {
                    $(this).closest('.m_fields').find('.field_settings').slideToggle();
                });
                $('body').on('keyup', '.wpm_change_title_name', function () {
                    $(this).closest('.m_fields').find('.wpm_field_main_name').text($(this).val());
                });

                $('body').on('click', '.f_readonly > input', function (e) {
                    var $this_field_default_val = $(this).closest('.field_settings').find('.field_settings_wrapper .f_value > input').val();
                    $this_field_default_val = $.trim($this_field_default_val);
                    if ($this_field_default_val == '') {
                        alert("<?=__('To enable read-only mode, you must enter a default value for the field.', 'cupri')?>");
                        e.preventDefault();
                        return;
                    }
                });

            });
        </script>
        <?php


    }

    // $this->generate_field_html($i_new,array(),true)
    public function generate_field_html($i, $fields, $for_js = false)
    {
        ob_start();
        if ($for_js) {
            $fields['type'][$i] = "' + fields_counter + '";
            $fields['name'][$i] = "";
//            $i = '';
        }

        ?>
        <div class="m_fields field_type_<?php echo esc_attr($fields['type'][$i]);
        if ($for_js) {
            echo ' m_fields_start ';
        } ?>">
            <span title="حذف" class="wpm_del_field">-</span>
            <span class="wpm_field_main_name"><span
                        class="id">#<?php echo(empty($i) ? '' : (int)$i); ?></span>  <?php if (isset($fields['name'][$i]) && !empty($fields['name'][$i])) {
                    echo esc_html($fields['name'][$i]);
                } else {
                    _e('Untitled', 'cupri');
                } ?></span>
            <div class="field_settings">
                <div class="f_type">
                    <label><strong><?php _e('Field Type', 'cupri'); ?></strong></label>
                    <select class="f_type_select" name="wpm_fields[type][<?php echo(empty($i) ? '' : (int)$i); ?>]">
                        <option value="none"><?php _e('Select Field Type', 'cupri'); ?></option>
                        <?php
                        foreach ($this->fields as $field_types) {
                            ?>
                            <option value="<?php echo esc_attr($field_types['type']); ?>" <?php selected($fields['type'][$i], $field_types['type'], true); ?> > <?php echo esc_html($field_types['name']); ?> </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <h3><?php _e('General', 'cupri'); ?></h3>
                <label class="f_name"><strong><?php _e('Field name', 'cupri'); ?></strong> <input type="text"
                                                                                                  value="<?php echo isset($fields['name'][$i]) ? esc_attr($fields['name'][$i]) : ''; ?>"
                                                                                                  class="wpm_change_title_name"
                                                                                                  name="wpm_fields[name][<?php echo(empty($i) ? '' : (int)$i); ?>]">
                </label>
                <label class="f_required"><strong><?php _e('Required?', 'cupri'); ?></strong> <input
                            type="checkbox" <?php if (isset($fields['required'][$i])) {
                        echo 'checked=checked';
                    } ?> name="wpm_fields[required][<?php echo(empty($i) ? '' : (int)$i); ?>]" value="1"> </label>
                <label class="f_desc"><strong><?php _e('Description', 'cupri'); ?></strong>
                    <input type="text"
                           value="<?php echo isset($fields['desc'][$i]) ? esc_attr($fields['desc'][$i]) : ''; ?>"
                           name="wpm_fields[desc][<?php echo(empty($i) ? '' : (int)$i); ?>]">


                </label>
                <label class="f_readonly">
                    <strong>(<?php _e('ReadOnly', 'cupri'); ?>)</strong>
                    <input
                            type="checkbox" <?php if (isset($fields['readonly'][$i])) {
                        echo 'checked=checked';
                    } ?> name="wpm_fields[readonly][<?php echo(empty($i) ? '' : (int)$i); ?>]" value="1">
                </label>


                <!-- Text -->

                <div class="field_settings_wrapper field_text">
                    <hr>
                    <h3><?php _e('Textbox', 'cupri'); ?></h3>
                    <label class="f_value">
                        <strong><?php _e('Default Value', 'cupri'); ?></strong>
                        <input type="text"
                               value="<?php echo isset($fields['text_default'][$i]) ? esc_attr($fields['text_default'][$i]) : ''; ?>"
                               name="wpm_fields[text_default][<?php echo(empty($i) ? '' : (int)$i); ?>]">
                    </label>
                    <br>
                    <label class="f_placeholder">
                        <strong><?php _e('Placeholder', 'cupri'); ?></strong>
                        <input type="text"
                               value="<?php echo isset($fields['text_placeholder'][$i]) ? esc_attr($fields['text_placeholder'][$i]) : ''; ?>"
                               name="wpm_fields[text_placeholder][<?php echo(empty($i) ? '' : (int)$i); ?>]">
                    </label>

                </div>

                <!-- multi_line_text -->

                <div class="field_settings_wrapper field_multi_line_text">
                    <hr>
                    <h3><?php _e('Textbox', 'cupri'); ?></h3>
                    <label class="f_value">
                        <strong><?php _e('Default Value', 'cupri'); ?></strong>
                        <textarea name="wpm_fields[text_default][<?php echo(empty($i) ? '' : (int)$i); ?>]" cols="30"
                                  rows="10"><?php echo isset($fields['text_default'][$i]) ? esc_textarea($fields['text_default'][$i]) : ''; ?></textarea>
                    </label>
                    <br>
                    <label class="f_placeholder">
                        <strong><?php _e('Placeholder', 'cupri'); ?></strong>
                        <textarea name="wpm_fields[text_placeholder][<?php echo(empty($i) ? '' : (int)$i); ?>]"
                                  cols="30"
                                  rows="10"><?php echo isset($fields['text_placeholder'][$i]) ? esc_textarea($fields['text_placeholder'][$i]) : ''; ?></textarea>

                    </label>

                </div>

                <!-- Paragraph -->


                <div class="field_settings_wrapper field_paragraph">
                    <hr>
                    <h3><?php _e('Paragraph', 'cupri'); ?></h3>
                    <label class="f_value">
                        <strong><?php _e('Content', 'cupri'); ?></strong>
                        <textarea
                                name="wpm_fields[paragraph_content][<?php echo(empty($i) ? '' : (int)$i); ?>]"><?php echo isset($fields['paragraph_content'][$i]) ? esc_textarea($fields['paragraph_content'][$i]) : ''; ?></textarea>
                    </label>
                </div>

                <!-- Checkbox -->


                <div class="field_settings_wrapper field_checkbox">
                    <hr>
                    <h3><?php _e('Checkbox', 'cupri'); ?></h3>

                </div>

                <!-- Combobox -->


                <div class="field_settings_wrapper field_select">
                    <hr>
                    <h3><?php _e('Combobox', 'cupri'); ?></h3>
                    <label class="f_choices">
                        <?php
                        if (isset($fields['combobox_choices'][$i]) && is_array($fields['combobox_choices'][$i])) {
                            foreach ($fields['combobox_choices'][$i] as $c_choice) {
                                ?>
                                <div class="cobmobox_choices_wrapper">
                                    <strong>&nbsp;</strong>
                                    <input type="text" value="<?php echo esc_attr($c_choice); ?>"
                                           name="wpm_fields[combobox_choices][<?php echo(empty($i) ? '' : (int)$i); ?>][]">
                                    <span class="combo_add"
                                          data-current-id="<?php echo(empty($i) ? '' : (int)$i); ?>">+</span>
                                    <span class="combo_remove">-</span>
                                </div>
                                <?php
                            }

                        } else {
                            ?>
                            <div class="cobmobox_choices_wrapper">
                                <strong>&nbsp;</strong>
                                <input type="text" value=""
                                       name="wpm_fields[combobox_choices][<?php echo(empty($i) ? '' : (int)$i); ?>][]">
                                <span class="combo_add"
                                      data-current-id="<?php echo(empty($i) ? '' : (int)$i); ?>">+</span>
                                <!-- <span class="combo_remove">-</span> -->
                            </div>
                            <?php
                        }

                        ?>
                    </label>

                </div>


            </div>
        </div>
        <?php
        $return = ob_get_clean();
        return str_replace(array("\n", "\r"), '', $return);
    }

    public function generate_all_fields_html()
    {
        ob_start();
        foreach ($this->fields as $field) {
            $this->generate_field_html('', $field, array());
        }
        $return = ob_get_clean();
        return str_replace(array("\n", "\r"), '', $return);
    }
}


