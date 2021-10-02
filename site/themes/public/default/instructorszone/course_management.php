<?php
global $multilingualFields;

$courseManager = jack_obj('dev_course_management');

$user_id = $_config['user']['pk_user_id'];

$args = array(
    'fk_parent_id' => $user_id
);
$pre_data = $courseManager->get_courses($args);
?>
<h4 class="text-center p-4">Course Management</h4>
<table class="table table-dark">
    <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Course Name</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($pre_data['data'] as $value) {
            ?>
            <tr>
                <th scope="row"><?php echo $value['pk_item_id'] ?></th>
                <td><?php echo processToRender($value['item_title']) ?></td>
                <td class="text-capitalize"><?php echo $value['publication_status'] ? $value['publication_status'] : 'Undefined' ?></td>
                <td>
                    <a href="update_course?id=<?php echo $value['pk_item_id'] ?>" class="btn btn-primary">Edit</a>
                    <a href="" class="btn btn-danger">Delete</a>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>