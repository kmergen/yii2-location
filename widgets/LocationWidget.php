<?php

namespace kmergen\location\widgets;

use yii\base\Widget;
use kmergen\location\helpers\Geo;
use yii\helpers\Html;

/**
 * LocationWidget represents a address model (e.g. postcode, city, etc. for a given location model instance
 * It renders the form elements for the model
 * Use it in horizontal forms only together with ActiveForm
 */
class LocationWidget extends Widget
{

    /**
     * @var object The location model
     */
    public $model;

    /**
     * @var object The form object (in most cases an ActiveForm instance)
     */
    public $form;

    /**
     * @var string
     */
    public $title = '';

    /**
     * @var string The formName of the model. This is the name which will use for creating the name of the input field.
     * If you only have one location widget in your form you may not set it, but if you use multiple location widgets in one form you should set it.
     * If you then set the [[formName]] for example to 'locationDelivery' then the name of the input field will be LocationDelivery[attributeName].
     * If you set [[formName]] to '' than the formName is only the attribute name.
     * If [[formName]] is null (default value) than the return value of the models formName() method is used to build the name of the input field. That is the common if
     * you only have one location widget in your form.
     */
    public $formName;

    /**
     * The follwing classes are only for horizontal forms.
     * The wrapper classes for the input postcode and city'.
     * We get the label cssClass from the fieldConfig
     * and helpBlock together is 12.
     */
    public $postcodeCssClass = 'col-md-2';
    public $cityCssClass = 'col-md-4';
    public $helpBlockCssClass = 'col-md-4';

    /**
     * The responsive part of the col class, we use this only for normal forms (not horizontal)
     * possible vlaues ('sm', 'md', 'lg'). e.g col-md-5
     * The prefix is normally in the whole form the same.
     * 
     * @see bootstrap grid
     */
    public $colPrefix = 'md';

    public function init()
    {
        
    }

    public function run()
    {
        if (isset($this->form->options['class']) && strpos($this->form->options['class'], 'form-horizontal') !== false) {
            echo $this->renderAddressHorizontal();
        } else {
            echo $this->renderAddress();
        }


//        $this->render('address', array(
//            'model' => $this->model,
//            'modelName' => $this->modelName,
//            'attributes' => $this->attributes,
//            'form' => $this->form,
//        ));
    }

    /**
     * Render the form part for this address
     */
    protected function renderAddress()
    {


        $html = '';

        if ($this->model->isAttributeActive('street')) {
            $html .= $this->form->field($this->model, 'street')->textInput(['name' => $this->getInputName('street')]);
        }

        if ($this->model->isAttributeActive('city')) {
            $html .= Html::beginTag('div', ['class' => 'row']);
            $html .= $this->form->field($this->model, 'postcode', ['options' => ['class' => 'col-' . $this->colPrefix . '-4']])->textInput(['name' => $this->getInputName('postcode')]);
            $html .= $this->form->field($this->model, 'city', ['options' => ['class' => 'col-' . $this->colPrefix . '-8']])->textInput(['name' => $this->getInputName('city')]);
            $html .= Html::endTag('div');
        }

        if ($this->model->isAttributeActive('state')) {
            $html .= $this->form->field($this->model, 'state')->textInput(['name' => $this->getInputName('state')]);
        }
        if ($this->model->isAttributeActive('country')) {
            $html .= $this->form->field($this->model, 'country')->dropDownList(Geo::IsoCountryList(), ['name' => $this->getInputName('country')]);
        }

        return $html;
    }

    /**
     * Render the form part for this address if the form is a horizontal form
     */
    protected function renderAddressHorizontal()
    {
        $html = '';

        if ($this->model->isAttributeActive('street')) {
            $html .= $this->form->field($this->model, 'street')->textInput();
        }

        if ($this->model->isAttributeActive('city')) {

            $required = ($this->model->isAttributeRequired('postcode') || $this->model->isAttributeRequired('house_no') ) ? ' required' : '';
            $inputOptions = isset($this->form->fieldConfig['inputOptions']) ? $this->form->fieldConfig['inputOptions'] : ['class' => 'form-control'];

            if ($this->model->hasErrors('postcode') || $this->model->hasErrors('city')) {
                $hasErrors = ' has-error';
            } else {
                $hasErrors = '';
            }

            $html .= '<div class="form-group' . $required . $hasErrors . '">';
            $html .= Html::label($this->model->getAttributeLabel('postcode') . ', ' . $this->model->getAttributeLabel('city'), null, $this->form->fieldConfig['labelOptions']);


            $html .= "<div class=\"{$this->postcodeCssClass}\">";
            $html .= Html::activeInput('text', $this->model, 'postcode', $inputOptions);
            $html .= '</div>';

            $html .= "<div class=\"{$this->cityCssClass}\">";
            $html .= Html::activeInput('text', $this->model, 'city', $inputOptions);
            $html .= '</div>';

            $html .= "<div class=\"{$this->helpBlockCssClass}\">";
            $html .= '<div class="help-block">';
            $html .= Html::error($this->model, 'postcode');
            $html .= Html::error($this->model, 'city');
            $html .= '</div>';
            $html .= '</div>';

            $html .= '</div>';
        }

        if ($this->model->isAttributeActive('state')) {
            $html .= $this->form->field($this->model, 'state')->textInput();
        }
        if ($this->model->isAttributeActive('country')) {
            $html .= $this->form->field($this->model, 'country')->dropDownList(Geo::IsoCountryList());
        }

        return $html;
    }

    function createLabel($attributes)
    {
        $label = '';
        $count = count($attributes);

        for ($i = 0; $i < $count; $i++) {
            $label .= Html::activeLabel($this->model, $attributes[$i]);
            if ($i < ($count - 1)) {
                $label .= ', ';
            }
        }
        return $label;
    }

    /**
     * Create an input name for the given attribute
     * 
     * @param string the name of the attribute.
     */
    protected function getInputName($attribute)
    {
        $formName = $this->formName;
        
        if ($formName === null) {
            return $this->model->formName() . "[$attribute]";
        } elseif ($formName === '') {
            return $attribute;
        } else {
            return $formName . "[$attribute]";
        }
    }

}
