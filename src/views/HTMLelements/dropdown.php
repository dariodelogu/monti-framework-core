<?php
    $id = $id ?? \App\Classes\Str::rand_letters_string();
    $attributes = $attributes ?? [];
    $align_right = isset($align_right) && is_bool($align_right) ? $align_right : false;
?>
<div class="dropdown" <?=generate_html_attributes($attributes)?>>
    <button class="btn dropdown-toggle <?=$button_class ?? ""?>" type="button" id="<?=$id?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=$button_text?></button>
    <div class="dropdown-menu <?=$align_right ? "dropdown-menu-end" : ""?>" aria-labelledby="<?=$id?>">
        <?php foreach($items as $item):
                $attributes = $item["attributes"] ?? [];
                $attributes["class"] = "dropdown-item " . ($attributes["class"] ?? ""); 
            ?>
            <a href="<?=$item["link"] ?? "javascript:void(0);"?>" <?=generate_html_attributes($attributes)?>><?=$item["text"]?></a>
        <?php endforeach; ?>
    </div>
</div>