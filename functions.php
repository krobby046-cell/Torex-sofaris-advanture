// Add custom post type for bookings
function create_booking_post_type() {
    register_post_type('booking',
        array(
            'labels' => array(
                'name' => __('Bookings'),
                'singular_name' => __('Booking')
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'custom-fields'),
            'menu_icon' => 'dashicons-carrot', // Icon for the menu
        )
    );
}
add_action('init', 'create_booking_post_type');

// Create a booking form shortcode
function booking_form_shortcode() {
    ob_start();
    ?>
    <form id="booking-form" method="post">
        <label for="customer_name">Name:</label>
        <input type="text" id="customer_name" name="customer_name" required>

        <label for="customer_email">Email:</label>
        <input type="email" id="customer_email" name="customer_email" required>

        <label for="booking_date">Booking Date:</label>
        <input type="date" id="booking_date" name="booking_date" required>

        <input type="submit" name="submit_booking" value="Book Now">
    </form>
    <?php

    if (isset($_POST['submit_booking'])) {
        $name = sanitize_text_field($_POST['customer_name']);
        $email = sanitize_email($_POST['customer_email']);
        $date = sanitize_text_field($_POST['booking_date']);

        // Create a new booking entry
        $post_id = wp_insert_post(array(
            'post_title' => $name,
            'post_content' => "Booking for: {$date}",
            'post_status' => 'publish',
            'post_type' => 'booking',
        ));

        // Send email notification
        wp_mail($email, 'Booking Confirmation', 'Thank you for your booking on ' . $date);
        wp_mail(get_option('admin_email'), 'New Booking', 'New booking from ' . $name . ' on ' . $date);

        echo "<h3>Thank you for your booking!</h3>";
    }

    return ob_get_clean();
}
add_shortcode('booking_form', 'booking_form_shortcode');

// Create admin menu for bookings management
function booking_management_menu() {
    add_menu_page('Booking Management', 'Bookings', 'manage_options', 'booking-management', 'booking_management_page', 'dashicons-carrot', 6);
}
add_action('admin_menu', 'booking_management_menu');

function booking_management_page() {
    $args = array('post_type' => 'booking', 'posts_per_page' => -1);
    $bookings = new WP_Query($args);

    echo '<h1>Manage Bookings</h1>';
    if ($bookings->have_posts()) {
        echo '<ul>';
        while ($bookings->have_posts()) {
            $bookings->the_post();
            echo '<li>' . get_the_title() . ' - ' . get_the_content() . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No bookings found.</p>';
    }
    wp_reset_postdata();
}