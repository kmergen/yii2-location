<?php

namespace kmergen\location\widgets;

use yii\base\Widget;
use yii\helpers\Html;

/**
 * ProximitySearch widget provides elements e.g. postcode distance for a search form
 * 
 * @author Klaus Mergen <kmergenweb@gmail.com>
 */
class ProximitySearch extends Widget
{

    public $labelPostcode = '';
    public $labelClass = '';
    public $distanceValues = ['' => '', 10 => '10 km', 20 => '20 km', 50 => '50 km', 100 => '100 km'];

    //public $fields=array(); //Which fields do you want to show (zone=>zone, street=>street, postcode=>postcode, city=>city) The value is the attribute for this field from the model

    public function init()
    {
        $this->registerClientScript();
    }

    public function run()
    {
        echo $this->renderControls();
    }

    protected function renderControls()
    {
        $html = '';
        $html .= '<div class="form-group">';
            if ($this->labelPostcode) {
                $html .= Html::label($this->labelPostcode, 'proxPostcode', ['class' => 'lbl-prox-postcode ' . $this->labelClass]);
            }
            $html .= Html::textInput('proxPostcode', isset($_GET['proxPostcode']) ? $_GET['proxPostcode'] : '', ['id' => 'proxPostcode', 'class' => 'form-control']);
        $html .= '</div>';
        
        $html .= '<div class="form-group">';
           $html .= Html::dropDownList('proxDistance', isset($_GET['proxDistance']) ? $_GET['proxDistance'] : '', $this->distanceValues, ['id' => 'proxDistance', 'class' => 'form-control']);
        $html .= '</div>';
        
        return $html;
    }

    protected function registerClientScript()
    {
        $js = " var proxPostcode=$('#proxPostcode');
                var proxDistance=$('#proxDistance');
  
        if (proxPostcode.val() == '') {
            proxDistance.attr('disabled', 'disabled').val('');  
            proxPostcode.addClass('prox-info').val('PLZ');
        }
  
        proxPostcode.focus(function(){
            $(this).removeClass('prox-info').val('');
        });
  
        proxPostcode.blur(function(){
            if ($(this).val() == '' || isNaN($(this).val())) {
                $(this).addClass('prox-info').val('PLZ');
                proxDistance.val('');
                proxDistance.attr('disabled', 'disabled');
            }
        });
	
        proxPostcode.keyup(function(){
            if (!isNaN($(this).val()) && $(this).val() != '') {
                proxDistance.removeAttr('disabled');
            } else {
                proxDistance.attr('disabled', 'disabled');
            }
        });
	
        $('form').submit(function(){
            if (proxPostcode.val() == 'PLZ') {
                proxPostcode.val('');
            }
        });";

        $css = "#proxPostcode {width:70px; margin-right:3px;}
                #proxDistance {width:100px; margin-right:5px;};
                .prox-info {font-style:italic;color:#ccc;}";

        $view = $this->getView();

        $view->registerCss($css);
        $view->registerJs($js);
    }

}
