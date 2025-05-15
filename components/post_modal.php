<?php
// Handle post submission
if (isset($_POST['submit_post'])) {
    // Debug information
    error_log("Post form submitted: " . print_r($_POST, true));

    if (isset($_POST['post_id'])) {
        // Update existing post
        $result = update_post($_POST, $_FILES);
    } else {
        // Create new post
        $result = create_post($_POST, $_FILES);
    }

    if ($result['success']) {
        set_flash_message('success', $result['message']);
    } else {
        set_flash_message('error', $result['message']);
    }

    // Redirect to appropriate page
    if (is_admin()) {
        redirect('admin.php');
    } else {
        redirect('my_posts.php');
    }
}

// Only show the modal if show_post_modal is set
if (isset($show_post_modal) && $show_post_modal):
    $is_edit_mode = isset($edit_post) && $edit_post;
    $modal_title = $is_edit_mode ? 'Edit Post' : 'Create New Post';
    $modal_icon = $is_edit_mode ? 'edit' : 'plus';
    $submit_text = $is_edit_mode ? 'Update Post' : 'Create Post';
    $submit_icon = $is_edit_mode ? 'save' : 'plus-circle';
    ?>
    <div class="modal fade show d-block" id="postModal" tabindex="-1" style="display:block;" aria-modal="true"
        role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content position-relative">
                <div class="modal-header recent sticky-subheader">
                    <h5 class="modal-title">
                        <i class="fas fa-<?php echo $modal_icon; ?> me-2"></i> <?php echo $modal_title; ?>
                    </h5>
                    <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="btn-close"></a>
                </div>

                <!-- Main Content and Tips Side by Side -->
                <div class="modal-body d-flex flex-wrap flex-md-nowrap">
                    <!-- Post Form (left side) -->
                    <div class="flex-grow-1 pe-md-4" style="min-width: 300px;">
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST"
                            enctype="multipart/form-data">
                            <?php if ($is_edit_mode): ?>
                                <input type="hidden" name="post_id" value="<?php echo $edit_post['post_id']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                    value="<?php echo $is_edit_mode ? html_escape($edit_post['title']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="6"
                                    required><?php echo $is_edit_mode ? html_escape($edit_post['content']) : ''; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php
                                    // Get categories
                                    $categories = get_categories();

                                    foreach ($categories as $category):
                                        $selected = ($is_edit_mode && $edit_post['r_category_id'] == $category['category_id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $category['category_id']; ?>" <?php echo $selected; ?>>
                                            <?php echo html_escape($category['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="image_upload" class="form-label">Featured Image</label>
                                <input type="file" class="form-control" id="image_upload" name="image_upload"
                                    accept="image/*">
                                <?php if ($is_edit_mode && !empty($edit_post['image_link'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo html_escape($edit_post['image_link']); ?>" alt="Current image"
                                            class="img-thumbnail" style="max-height: 100px;">
                                        <input type="hidden" name="existing_image"
                                            value="<?php echo html_escape($edit_post['image_link']); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="form-text">Upload an image for your post (JPG, PNG, GIF up to 2MB)</div>
                            </div>

                            <div class="mb-3">
                                <label for="post_status" class="form-label">Status</label>
                                <?php if (is_admin()): ?>
                                    <select class="form-select" id="post_status" name="post_status">
                                        <option value="draft" <?php echo ($is_edit_mode && $edit_post['post_status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                        <option value="published" <?php echo ($is_edit_mode && $edit_post['post_status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                    </select>
                                <?php else: ?>
                                    <input class="form-control" type="text" id="post_status_display"
                                        value="<?php echo $is_edit_mode ? ucfirst($edit_post['post_status']) : 'Draft'; ?>"
                                        readonly>
                                    <input type="hidden" name="post_status"
                                        value="<?php echo $is_edit_mode ? $edit_post['post_status'] : 'draft'; ?>">
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" name="submit_post" class="btn manual-button">
                                    <i class="fas fa-<?php echo $submit_icon; ?> me-1"></i> <?php echo $submit_text; ?>
                                </button>
                                <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="btn manual-button cancel">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Tips for Authors (right side) -->
                    <div class="tips-box bg-light border rounded p-3 mt-4 mt-md-0"
                        style="width: 250px; font-size: 0.875rem;">
                        <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb me-2"></i>Tips for Authors</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Use clear, descriptive
                                titles</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Add relevant images</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Choose the right category
                            </li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Use paragraphs for
                                readability</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i> Save as draft before publishing</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
<?php endif; ?>