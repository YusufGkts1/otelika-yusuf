<?php 

use \model\system\Setting;

use \model\system\log\LoggingService;

class ControllerSystemSetting extends RestEndpoint {
    protected function get() {
        $setting = new Setting();

        $general_settings = $setting->getSettings('general');
        $email_settings = $setting->getSettings('email');
        $other_settings = $setting->getSettings('other');

        $data = array(
            'type' => 'setting',
            'attributes' => array(

            )
        );

        foreach($general_settings as $setting)
            $data['attributes']['general'][$setting['key']] = $setting['value'];

        foreach($email_settings as $setting)
            $data['attributes']['email'][$setting['key']] = $setting['value'];

        foreach($other_settings as $setting)
            $data['attributes']['other'][$setting['key']] = $setting['value'];

        $this->success(array(
            'data' => $data
        ));
    }

    protected function post() {
        $this->notImplemented();
    }

    protected function patch() {
        if(null == $this->data()) {
            $this->badRequest("Post body parameter 'data' is missing");
            return;
        }

        if(null == $this->data()->attributes) {
            $this->badRequest("'attributes' is missing");
            return;
        }

        $attr = $this->data()->attributes;

        $setting = new Setting(new LoggingService());

        /* command */

        foreach($attr as $category => $values) {
            foreach($values as $key => $value) {
                $setting->changeSetting($key, $value);
            }
        }

        /* query */

        $data = array(
            'type' => 'setting',
            'attributes' => array()
        );

        foreach($attr as $category => $values) {
            foreach($values as $key => $value) {
                $data['attributes'][$category][$key] = $setting->getSetting($key);
            }
        }

        $this->success(array(
            'data' => $data
        ));
    }

    protected function delete() {
        $this->notImplemented();
    }

    protected function submoduleId() : int {
        return 3;
    }

    protected function filterSupportingFields() : array {
        return array();
    }

    protected function orderBySupportingFields() : array {
        return $this->filterSupportingFields();
    }
}

?>