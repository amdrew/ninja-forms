<?php if ( ! defined( 'ABSPATH' ) ) exit;

final class NF_Admin_Menus_Forms extends NF_Abstracts_Menu
{
    public $page_title = 'New Form';

    public $menu_slug = 'ninja-forms';

    public $icon_url = 'dashicons-feedback';

    public function __construct()
    {
        parent::__construct();

        add_action( 'admin_menu', array( $this, 'submenu_separators' ), 9000 );
    }

    public function display()
    {
        Ninja_Forms::template( 'admin-menu-new-form.html.php' );
        wp_enqueue_style( 'nf-builder', Ninja_Forms::$url . 'assets/css/builder.css' );

        wp_enqueue_script( 'backbone-marionette', Ninja_Forms::$url . 'assets/js/lib/backbone.marionette.min.js', array( 'jquery', 'backbone' ) );
        wp_enqueue_script( 'backbone-radio', Ninja_Forms::$url . 'assets/js/lib/backbone.radio.min.js', array( 'jquery', 'backbone' ) );
        wp_enqueue_script( 'jquery-perfect-scrollbar', Ninja_Forms::$url . 'assets/js/lib/perfect-scrollbar.jquery.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'jquery-hotkeys-new', Ninja_Forms::$url . 'assets/js/lib/jquery.hotkeys.js' );
        
        wp_enqueue_script( 'requirejs', Ninja_Forms::$url . 'assets/js/lib/require.js', array( 'jquery', 'backbone' ) );
        wp_enqueue_script( 'nf-builder', Ninja_Forms::$url . 'assets/js/builder/main.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable' ) );
        
        wp_localize_script( 'nf-builder', 'nfAdmin', array( 'ajaxNonce' => wp_create_nonce( 'ninja_forms_ajax_nonce' ), 'requireBaseUrl' => Ninja_Forms::$url . 'assets/js/', 'previewurl' => site_url() . '/?nf_preview_form=' . $_GET[ 'form_id' ] ) );

        delete_user_option( get_current_user_id(), 'nf_form_preview_' . $_GET[ 'form_id' ] );

        $this->_localize_form_data( $_GET[ 'form_id' ] );

        $this->_localize_field_type_data();

        $this->_localize_action_type_data();

    }

    public function submenu_separators()
    {
        add_submenu_page( 'ninja-forms', '', '', 'read', '', '' );
    }

    private function _localize_form_data( $form_id )
    {
        $form = Ninja_Forms()->form( $form_id )->get();
        $fields = Ninja_Forms()->form( $form_id )->get_fields();
        $actions = Ninja_Forms()->form( $form_id )->get_actions();

        $fields_settings = array();

        foreach( $fields as $field ){
            $settings = $field->get_settings();
            $settings[ 'id' ] = $field->get_id();
            $fields_settings[] = $settings;
        }

        $actions_settings = array();

        foreach( $actions as $action ){
            $actions_settings[] = $action->get_settings();
        }
        
        $form_data = array();
        $form_data['id'] = $form_id;
        $form_data['settings'] = $form->get_settings();
        $form_data['fields'] = $fields_settings;
        $form_data['actions'] = $actions_settings;

        ?>
        <script>
            var preloadedFormData = <?php echo wp_json_encode( $form_data ); ?>;
            // console.log( preloadedFormData );
        </script>
        <?php
    }

    private function _localize_field_type_data()
    {
        $field_type_settings = array();

        foreach( Ninja_Forms()->fields as $field ){

            $name = $field->get_name();

            $settings = $field->get_settings();

            $settings[ 'parentType' ] = $field->get_parent_type();

            $field_type_settings[ $name ] = $settings;
        }
        ?>
        <script>
            var fieldTypeData = <?php echo wp_json_encode( $field_type_settings ); ?>;
            // console.log( fieldTypeData );
        </script>
        <?php
    }

    private function _localize_action_type_data()
    {
        $action_type_settings = array();

        foreach( Ninja_Forms()->actions as $action ){

            $name = $action->get_name();

            $settings = $action->get_settings();

            $action_type_settings[ $name ] = $settings;
        }
        ?>
        <script>
            var actionTypeData = <?php echo wp_json_encode( $action_type_settings ); ?>;
            // console.log( actionTypeData );
        </script>
        <?php
    }

}
