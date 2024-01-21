<?php /** @noinspection PhpUnused */

namespace libs;

class drawForms
{

    private stack $stack;
    private bool $row = false;

    public function __construct()
    {
        $this->stack = new stack();
    }

    public function form(string $cmd, string $method, string $class = null, string $target = null): void
    {
        echo '<form action="' . $cmd . '" ';
        if (!is_null($class)) {
            echo 'class="' . $class . '" ';
        }
        if (!is_null($target)) {
            echo 'target="' . $target . '" ';
        }
        echo ' method="' . $method . '">';
        $this->stack->push('</form>');
        if ($class == "row") {
            $this->row = true;
        }
    }

    public function eof(): void
    {
        $c = $this->stack->count();
        for ($i = 0; $i < $c; $i++) {
            echo $this->stack->pop();
        }
    }

    public function pop(): void
    {
        echo $this->stack->pop();
    }

    public function groupForm(array $input): void
    {
        // label=>"", type="", name="", placeholder=>"", value="", "disabled"=>false, "class"=""
        $this->divGroup();
        foreach ($input as $item) {
            $this->inputItem($item);
        }
        $this->pop();
    }

    public function divGroup($class = null): void
    {
        if (is_null($class)) {
            echo '<div class="form-group">';
        } else {
            echo '<div class="' . $class . '">';
        }
        $this->stack->push("</div>");
    }

    public function inputItem(array $item): void
    {
        if ($this->row) {
            echo '<div class="col">';
        }
        echo '<label  class="form-label" for="' . $item['name'] . '">' . $item['label'] . '</label>';
        echo '<input type="' . $item['type'] . '" class="form-control ';
        if (isset($item['class'])) {
            echo $item['class'];
        }
        echo '" id="' . $item['name'] . '" name="' . $item['name'] . '" placeholder="'
            . $item['placeholder'] . '" value="' . $item['value'] . '"';
        if (isset($item['disabled'])) {
            if ($item['disabled']) {
                echo ' disabled ';
            }
        }
        echo '>';
        if ($this->row) {
            echo '</div>';
        }
    }

    public function input(
        string      $type,
        string      $name,
        string      $value,
        string|null $label,
        string      $placeholder,
        string|null $class = null,
        bool        $disabled = false,
        array       $additionalParams = []
    ): void
    {
        if ($this->row) {
            echo '<div class="col">';
        }
        if (!is_null($label)) {
            echo '<label  class="form-label" for="' . $name . '">' . $label . '</label>';
        }
        echo '<input type="' . $type . '" class="form-control ';
        if (isset($class)) {
            echo $class;
        }
        echo '" name="' . $name . '" placeholder="' . $placeholder . '" value="' . $value . '"';
        if ($disabled) {
            echo ' disabled ';
        }
        foreach ($additionalParams as $key => $value) {
            echo ' ' . $key . '="' . $value . '" ';
        }
        echo '>';
        if ($this->row) {
            echo '</div>';
        }
    }

    public function select(string|null $label, string $name, array $items, string $selectedValue): void
    {
        if ($this->row) {
            echo '<div class="col">';
        }
        if (!is_null($label)) {
            echo '<label for="' . $name . '">' . $label . '</label>';
        }
        echo '<select class="form-control" id="' . $name . '" name="' . $name . '">';
        foreach ($items as $item) {
            echo '<option value="' . $item['value'] . '"';
            if ($item['value'] == $selectedValue) {
                echo ' selected';
            }
            echo '>' . $item['item'] . '</option>';
        }
        echo '</select>';
        if ($this->row) {
            echo '</div>';
        }
    }

    public function submit($name, $value, $label, $class = "btn-primary"): void
    {
        if ($this->row) {
            echo '<div class="col"><span class="form-label" style="display: inline-block; margin-bottom: .5rem;">&nbsp;</span><br>';
        }
        if (is_null($name)) {
            echo '<button class="btn ' . $class . '">' . $label . '</button>';
        } else {
            echo '<button class="btn ' . $class . '" name="' . $name . '" value="' . $value . '">' . $label . '</button>';
        }
        if ($this->row) {
            echo '</div>';
        }
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function buttonGroup(string $commonLabel, array $items): void
    {
        if ($this->row) {
            echo '<div class="col">';
        }
        //item=array("colorClass"="btn-primary", "name"=>"", value=>"", "label"=>"")
        $this->divGroup("btn-group");
        foreach ($items as $item) {
            echo '<button type="submit" class="btn ' . $item['colorClass'] . '"';
            if (strlen($item['name'] > 0)) {
                echo ' name="' . $item['name'] . '" ';
            }
            if (strlen($item['value'] > 0)) {
                echo ' value="' . $item['value'] . '" ';
            }
            echo '>' . $item['label'] . '</button>';
        }
        $this->pop();
        if ($this->row) {
            echo '</div>';
        }
    }

    public function inputHidden($name, $value): void
    {
        echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
    }

    public function checkBox($name, $label, bool $checked): void
    {
        echo '<div class="form-check">
  			<input class="form-check-input" type="checkbox" value="" name="' . $name . '" id="' . $name . '"';
        if ($checked) {
            echo ' checked';
        }
        echo '><label class="form-check-label" for="' . $name . '">' . $label . '</label>
			</div>';
    }

    public function textArea(string $name, string $value, int $rows = null, string $textLabel = null): void
    {
        echo '<div class="form-group">';
        if (!is_null($textLabel)) {
            echo '<label for="' . $name . '">' . $textLabel . '</label>';
        }
        echo '<textarea class="form-control" name="' . $name . '"';
        if (!is_null($rows)) {
            echo ' rows="' . $rows . '"';
        }
        echo '>' . $value . '</textarea>';
        echo '</div>';
    }
}
