<?php
global $post;

$args = array(
    'role'    => 'moderator',
    'order'   => 'ASC'
);
$moderators = get_users($args);
$selected_moderator = get_post_meta($post->ID, 'assigned_moderator', true);
?>

<select name="assigned_moderator" id="assigned_moderator" class="assigned-moderator-select-list">
    <option></option>
    <?php foreach ($moderators as $moderator) :
        $id = $moderator->ID;
    ?>
        <option value="<?php echo esc_attr($id); ?>" <?php selected($selected_moderator, esc_attr($id)); ?>><?php echo esc_html($moderator->display_name); ?></option>
    <?php endforeach; ?>
</select>