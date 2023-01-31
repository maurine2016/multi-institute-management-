<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

class WL_MIM_Administrator {
	/* Get administrator data to display on table */
	public static function get_administrator_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$data = get_users( array(
			'meta_key'   => 'wlim_institute_id',
			'meta_value' => $institute_id
		) );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id = $row->ID;
				if ( user_can( $id, 'manage_options' ) ) {
					continue;
				}
				$first_name  = get_user_meta( $id, 'first_name', true ) ? get_user_meta( $id, 'first_name', true ) : '-';
				$last_name   = get_user_meta( $id, 'last_name', true ) ? get_user_meta( $id, 'last_name', true ) : '-';
				$username    = $row->user_login;
				$permissions = array_intersect_key( WL_MIM_Helper::get_capabilities(), array_flip( array_intersect( array_keys( ( new WP_User( $id ) )->allcaps ), array_keys( WL_MIM_Helper::get_capabilities() ) ) ) );
				foreach ( $permissions as $key => $value ) {
					if ( ! current_user_can( $key ) ) {
						unset( $permissions[ $key ] );
					}
				}
				$permissions = implode( '<br>', $permissions );
				$added_on    = date_format( date_create( $row->user_registered ), "d-m-Y g:i A" );

				$results["data"][] = array(
					esc_html( $first_name ),
					esc_html( $last_name ),
					esc_html( $username ),
					$permissions,
					esc_html( $added_on ),
					'<a class="mr-3" href="#update-administrator" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a>'
				);
			}
		}

		if ( ! isset( $results["data"] ) ) {
			$results["data"] = array();
		}

		wp_send_json( $results );
	}

	/* Add new administrator */
	public static function add_administrator() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-administrator'], 'add-administrator' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$first_name       = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$last_name        = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
		$username         = isset( $_POST['username'] ) ? sanitize_text_field( $_POST['username'] ) : '';
		$password         = isset( $_POST['password'] ) ? $_POST['password'] : '';
		$password_confirm = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';
		$permissions      = ( isset( $_POST['permissions'] ) && is_array( $_POST['permissions'] ) ) ? $_POST['permissions'] : [];

		$salary           = number_format( isset( $_POST['salary'] ) ? max( floatval( sanitize_text_field( $_POST['salary'] ) ), 0 ) : 0, 2, '.', '' );
		$job_title        = isset( $_POST['job_title'] ) ? sanitize_text_field( $_POST['job_title'] ) : '';
		$job_description  = isset( $_POST['job_description'] ) ? sanitize_textarea_field( $_POST['job_description'] ) : '';
		$gender           = isset( $_POST['gender'] ) ? sanitize_text_field( $_POST['gender'] ) : '';
		$date_of_birth    = ( isset( $_POST['date_of_birth'] ) && ! empty( $_POST['date_of_birth'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['date_of_birth'] ) ) ) : null;
		$id_proof         = ( isset( $_FILES['id_proof'] ) && is_array( $_FILES['id_proof'] ) ) ? $_FILES['id_proof'] : null;
		$address          = isset( $_POST['address'] ) ? sanitize_textarea_field( $_POST['address'] ) : '';
		$city             = isset( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : '';
		$zip              = isset( $_POST['zip'] ) ? sanitize_text_field( $_POST['zip'] ) : '';
		$state            = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
		$nationality      = isset( $_POST['nationality'] ) ? sanitize_text_field( $_POST['nationality'] ) : '';
		$phone            = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		$qualification    = isset( $_POST['qualification'] ) ? sanitize_text_field( $_POST['qualification'] ) : '';
		$email            = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
		$photo            = ( isset( $_FILES['photo'] ) && is_array( $_FILES['photo'] ) ) ? $_FILES['photo'] : null;
		$signature        = ( isset( $_FILES['signature'] ) && is_array( $_FILES['signature'] ) ) ? $_FILES['signature'] : null;
		$is_active        = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;
		$add_staff_record = isset( $_POST['add_staff_record'] ) ? boolval( sanitize_text_field( $_POST['add_staff_record'] ) ) : 0;

		$errors = array();
		if ( empty( $username ) ) {
			$errors['username'] = esc_html__( 'Please provide username.', WL_MIM_DOMAIN );
		}

		if ( empty( $password ) ) {
			$errors['password'] = esc_html__( 'Please provide password.', WL_MIM_DOMAIN );
		}

		if ( empty( $password_confirm ) ) {
			$errors['password_confirm'] = esc_html__( 'Please confirm password.', WL_MIM_DOMAIN );
		}

		if ( $password !== $password_confirm ) {
			$errors['password'] = esc_html__( 'Passwords do not match.', WL_MIM_DOMAIN );
		}

		if ( ! array_intersect( $permissions, array_keys( WL_MIM_Helper::get_capabilities() ) ) == $permissions ) {
			wp_send_json_error( esc_html__( 'Please select valid permissions.', WL_MIM_DOMAIN ) );
		}

		if ( $add_staff_record ) {
			if ( empty( $salary ) ) {
				$errors['salary'] = esc_html__( 'Please specify salary.', WL_MIM_DOMAIN );
			}

			if ( empty( $job_title ) ) {
				$errors['job_title'] = esc_html__( 'Please specify job title.', WL_MIM_DOMAIN );
			}

			if ( ! empty( $email ) && ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$errors['email'] = esc_html__( 'Please provide a valid email address.', WL_MIM_DOMAIN );
			}
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'first_name' => $first_name,
					'last_name'  => $last_name,
					'user_login' => $username,
					'user_pass'  => $password
				);

				$user_id = wp_insert_user( $data );
				if ( is_wp_error( $user_id ) ) {
					wp_send_json_error( esc_html__( $user_id->get_error_message(), WL_MIM_DOMAIN ) );
				}

				$user = new WP_User( $user_id );
				foreach ( $permissions as $capability ) {
					if ( ! current_user_can( $capability ) ) {
						continue;
					}
					$user->add_cap( $capability );
				}

				if ( $add_staff_record ) {
					$inactive_at = null;
					if ( ! $is_active ) {
						$inactive_at = date( 'Y-m-d H:i:s' );
					}

					$data = array(
						'first_name'      => $first_name,
						'last_name'       => $last_name,
						'salary'          => $salary,
						'job_title'       => $job_title,
						'job_description' => $job_description,
						'last_name'       => $last_name,
						'gender'          => $gender,
						'date_of_birth'   => $date_of_birth,
						'address'         => $address,
						'city'            => $city,
						'zip'             => $zip,
						'state'           => $state,
						'nationality'     => $nationality,
						'phone'           => $phone,
						'qualification'   => $qualification,
						'email'           => $email,
						'user_id'         => $user_id,
						'is_active'       => $is_active,
						'inactive_at'     => $inactive_at,
						'added_by'        => get_current_user_id(),
						'institute_id'    => $institute_id
					);

					if ( ! empty( $id_proof ) ) {
						$id_proof = media_handle_upload( 'id_proof', 0 );
						if ( is_wp_error( $id_proof ) ) {
							throw new Exception( esc_html__( $id_proof->get_error_message(), WL_MIM_DOMAIN ) );
						}
						$data['id_proof'] = $id_proof;
					}

					if ( ! empty( $photo ) ) {
						$photo = media_handle_upload( 'photo', 0 );
						if ( is_wp_error( $photo ) ) {
							throw new Exception( esc_html__( $photo->get_error_message(), WL_MIM_DOMAIN ) );
						}
						$data['photo_id'] = $photo;
					}

					if ( ! empty( $signature ) ) {
						$signature = media_handle_upload( 'signature', 0 );
						if ( is_wp_error( $signature ) ) {
							throw new Exception( esc_html__( $signature->get_error_message(), WL_MIM_DOMAIN ) );
						}
						$data['signature_id'] = $signature;
					}

					$data['created_at'] = current_time( 'Y-m-d H:i:s' );

					$success = $wpdb->insert( "{$wpdb->prefix}wl_min_staffs", $data );
					if ( ! $success ) {
						throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
					}
				}

				update_user_meta( $user_id, 'wlim_institute_id', $institute_id );

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Administrator added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch administrator to update */
	public static function fetch_administrator() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->base_prefix}users WHERE ID = $id" );
		if ( ! $row ) {
			die();
		}

		if ( user_can( $id, 'manage_options' ) ) {
			die();
		}

		$user_institute_id = get_user_meta( $row->ID, 'wlim_institute_id', true );
		if ( $user_institute_id !== $institute_id ) {
			die();
		}

		$staff           = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_staffs WHERE is_deleted = 0 AND user_id = $id AND institute_id = $institute_id" );
		$salary          = $staff ? $staff->salary : 0;
		$job_title       = $staff ? $staff->job_title : null;
		$job_description = $staff ? $staff->job_description : null;
		$gender          = $staff ? $staff->gender : null;
		$date_of_birth   = $staff ? $staff->date_of_birth : null;
		$address         = $staff ? $staff->address : null;
		$city            = $staff ? $staff->city : null;
		$zip             = $staff ? $staff->zip : null;
		$state           = $staff ? $staff->state : null;
		$nationality     = $staff ? $staff->nationality : null;
		$qualification   = $staff ? $staff->qualification : null;
		$phone           = $staff ? $staff->phone : null;
		$email           = $staff ? $staff->email : null;

		$nonce = wp_create_nonce( "update-administrator-$id" );
		ob_start(); ?>
        <input type="hidden" name="update-administrator-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action" value="wl-mim-update-administrator">
        <div class="row">
            <div class="col form-group">
                <label for="wlim-administrator-first_name_update" class="col-form-label"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?>:</label>
                <input name="first_name" type="text" class="form-control" id="wlim-administrator-first_name_update" placeholder="<?php esc_html_e( "First Name", WL_MIM_DOMAIN ); ?>" value="<?php echo get_user_meta( $id, 'first_name', true ); ?>">
            </div>
            <div class="col form-group">
                <label for="wlim-administrator-last_name_update" class="col-form-label"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?>:</label>
                <input name="last_name" type="text" class="form-control" id="wlim-administrator-last_name_update" placeholder="<?php esc_html_e( "Last Name", WL_MIM_DOMAIN ); ?>" value="<?php echo get_user_meta( $id, 'last_name', true ); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="wlim-administrator-username_update" class="col-form-label"><?php esc_html_e( 'Username', WL_MIM_DOMAIN ); ?>:
                <small class="text-secondary"><em><?php esc_html_e( "cannot be changed.", WL_MIM_DOMAIN ); ?></em></small>
            </label>
            <input name="username" type="text" class="form-control" id="wlim-administrator-username_update" placeholder="<?php esc_html_e( "Username", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->user_login ); ?>" disabled>
        </div>
        <div class="form-group">
            <label for="wlim-administrator-password_update" class="col-form-label"><?php esc_html_e( 'Password', WL_MIM_DOMAIN ); ?>:</label>
            <input name="password" type="password" class="form-control" id="wlim-administrator-password_update" placeholder="<?php esc_html_e( "Password", WL_MIM_DOMAIN ); ?>">
        </div>
        <div class="form-group">
            <label for="wlim-administrator-password_confirm_update" class="col-form-label"><?php esc_html_e( 'Confirm Password', WL_MIM_DOMAIN ); ?>:</label>
            <input name="password_confirm" type="password" class="form-control" id="wlim-administrator-password_confirm_update" placeholder="<?php esc_html_e( "Confirm Password", WL_MIM_DOMAIN ); ?>">
        </div>
		<label class="col-form-label"><?php esc_html_e( 'Permissions', WL_MIM_DOMAIN ); ?>:
			<?php
			if ( user_can( $row->ID, WL_MIM_Helper::$core_capability ) ) { ?>
                <small class="text-secondary">
                    <em><?php esc_html_e( "cannot be changed for users with role 'Administrator'.", WL_MIM_DOMAIN ); ?></em>
                </small>
				<?php
			} ?>
        </label>
		<?php
		foreach ( WL_MIM_Helper::get_capabilities() as $capability_key => $capability_value ) {
			if ( ! current_user_can( $capability_key ) ) {
				continue;
			}
			?>
            <div class="form-check pl-0">
                <input name="permissions[]" class="position-static mt-0 form-check-input" type="checkbox" id="<?php echo esc_attr( $capability_key ) . "_update"; ?>" value="<?php echo esc_attr( $capability_key ); ?>" <?php echo user_can( $row->ID, WL_MIM_Helper::$core_capability ) ? 'disabled' : '' ?>  <?php if ( get_current_user_id() == $id ) {
					echo "disabled";
				} ?>>
                <label class="form-check-label" for="<?php echo esc_attr( $capability_key ) . "_update"; ?>"><?php esc_html_e( $capability_value, WL_MIM_DOMAIN ); ?></label>
            </div>
			<?php
		} ?>
        <hr>
        <div class="form-check pl-0">
            <input name="add_staff_record" class="position-static mt-0 form-check-input wlim-administrator-add_staff_record" type="checkbox" id="wlim-administrator-add_staff_record_update" <?php echo boolval( $staff ) ? "checked" : ""; ?>>
            <label class="form-check-label" for="wlim-administrator-add_staff_record_update">
                <strong class="text-primary"><?php esc_html_e( 'Add Staff Record?', WL_MIM_DOMAIN ); ?></strong>
            </label>
        </div>
        <div class="wlim-staff-record-fields">
            <hr>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="wlim-administrator-salary_update" class="col-form-label">* <?php esc_html_e( 'Salary', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="salary" type="number" class="form-control" id="wlim-administrator-salary_update" placeholder="<?php esc_html_e( "Salary", WL_MIM_DOMAIN ); ?>" min="0" step="any" value="<?php echo esc_attr( $salary ); ?>">
                </div>
                <div class="col-sm-6 form-group">
                    <label for="wlim-administrator-job_title_update" class="col-form-label">* <?php esc_html_e( 'Job Title', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="job_title" type="text" class="form-control" id="wlim-administrator-job_title_update" placeholder="<?php esc_html_e( "Job Title", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $job_title ); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="wlim-administrator-job_description_update" class="col-form-label"><?php esc_html_e( 'Job Description', WL_MIM_DOMAIN ); ?>:</label>
                <textarea name="job_description" class="form-control" rows="4" id="wlim-administrator-job_description_update" placeholder="<?php esc_html_e( "Job Description", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $job_description ); ?></textarea>
            </div>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label class="col-form-label"><?php esc_html_e( 'Gender', WL_MIM_DOMAIN ); ?>:</label><br>
                    <div class="row mt-2">
                        <div class="col-sm-12">
                            <label class="radio-inline mr-3"><input checked type="radio" name="gender" class="mr-2" value="male" id="wlim-administrator-male_update"><?php esc_html_e( 'Male', WL_MIM_DOMAIN ); ?> </label>
                            <label class="radio-inline"><input type="radio" name="gender" class="mr-2" value="female" id="wlim-administrator-female_update"><?php esc_html_e( 'Female', WL_MIM_DOMAIN ); ?> </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 form-group">
                    <label for="wlim-administrator-date_of_birth_update" class="col-form-label"><?php esc_html_e( 'Date of Birth', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="date_of_birth" type="text" class="form-control wlim-date_of_birth_update" id="wlim-administrator-date_of_birth_update" placeholder="<?php esc_html_e( "Date of Birth", WL_MIM_DOMAIN ); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="wlim-administrator-address_update" class="col-form-label"><?php esc_html_e( 'Address', WL_MIM_DOMAIN ); ?>:</label>
                    <textarea name="address" class="form-control" rows="4" id="wlim-administrator-address_update" placeholder="<?php esc_html_e( "Address", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $address ); ?></textarea>
                </div>
                <div class="col-sm-6 form-group">
                    <div>
                        <label for="wlim-administrator-city_update" class="col-form-label"><?php esc_html_e( 'City', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="city" type="text" class="form-control" id="wlim-administrator-city_update" placeholder="<?php esc_html_e( "City", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $city ); ?>">
                    </div>
                    <div>
                        <label for="wlim-administrator-zip_update" class="col-form-label"><?php esc_html_e( 'Zip Code', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="zip" type="text" class="form-control" id="wlim-administrator-zip_update" placeholder="<?php esc_html_e( "Zip Code", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $zip ); ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="wlim-administrator-state_update" class="col-form-label"><?php esc_html_e( 'State', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="state" type="text" class="form-control" id="wlim-administrator-state_update" placeholder="<?php esc_html_e( "State", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $state ); ?>">
                </div>
                <div class="col-sm-6 form-group">
                    <label for="wlim-administrator-nationality_update" class="col-form-label"><?php esc_html_e( 'Nationality', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="nationality" type="text" class="form-control" id="wlim-administrator-nationality_update" placeholder="<?php esc_html_e( "Nationality", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $nationality ); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="wlim-administrator-phone_update" class="col-form-label"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="phone" type="text" class="form-control" id="wlim-administrator-phone_update" placeholder="<?php esc_html_e( "Phone", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $phone ); ?>">
                </div>
                <div class="col-sm-6 form-group">
                    <label for="wlim-administrator-email_update" class="col-form-label"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="email" type="text" class="form-control" id="wlim-administrator-email_update" placeholder="<?php esc_html_e( "Email", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $email ); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="wlim-administrator-qualification_update" class="col-form-label"><?php esc_html_e( 'Qualification', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="qualification" type="text" class="form-control" id="wlim-administrator-qualification_update" placeholder="<?php esc_html_e( "Qualification", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $qualification ); ?>">
                </div>
                <div class="col-sm-6 form-group">
                    <label for="wlim-administrator-id_proof_update" class="col-form-label"><?php esc_html_e( 'ID Proof', WL_MIM_DOMAIN ); ?>:</label><br>
					<?php if ( ! empty ( $staff->id_proof ) ) { ?>
                        <a href="<?php echo wp_get_attachment_url( $staff->id_proof ); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><?php esc_html_e( 'View ID Proof', WL_MIM_DOMAIN ); ?></a>
                        <input type="hidden" name="id_proof_in_db" value="<?php echo esc_attr( $staff->id_proof ); ?>">
					<?php } ?>
                    <input name="id_proof" type="file" id="wlim-administrator-id_proof_update">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="wlim-administrator-photo_update" class="col-form-label"><?php esc_html_e( 'Photo', WL_MIM_DOMAIN ); ?>:</label><br>
					<?php if ( ! empty ( $staff->photo_id ) ) { ?>
                        <img src="<?php echo wp_get_attachment_url( $staff->photo_id ); ?>" class="img-responsive photo-signature">
                        <input type="hidden" name="photo_in_db" value="<?php echo esc_attr( $staff->photo_id ); ?>">
					<?php } ?>
                    <input name="photo" type="file" id="wlim-administrator-photo_update">
                </div>
                <div class="col-sm-6 form-group">
                    <label for="wlim-administrator-signature_update" class="col-form-label"><?php esc_html_e( 'Signature', WL_MIM_DOMAIN ); ?>:</label><br>
					<?php if ( ! empty ( $staff->signature_id ) ) { ?>
                        <img src="<?php echo wp_get_attachment_url( $staff->signature_id ); ?>" class="img-responsive photo-signature">
                        <input type="hidden" name="signature_in_db" value="<?php echo esc_attr( $staff->signature_id ); ?>">
					<?php } ?>
                    <input name="signature" type="file" id="wlim-administrator-signature_update">
                </div>
            </div>
            <div class="form-check pl-0">
                <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-administrator-is_active_update" <?php echo boolval( $staff->is_active ) ? "checked" : ""; ?>>
                <label class="form-check-label" for="wlim-administrator-is_active_update">
					<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                </label>
            </div>
        </div>
		<input type="hidden" name="administrator_id" value="<?php echo esc_attr( $row->ID ); ?>">
		<?php $html         = ob_get_clean();
		$wlim_date_selector = '.wlim-date_of_birth_update';
		$permissions        = array_intersect( array_keys( ( new WP_User( $id ) )->allcaps ), array_keys( WL_MIM_Helper::get_capabilities() ) );

		$json = json_encode( array(
			'wlim_date_selector'  => esc_attr( $wlim_date_selector ),
			'permissions'         => array_map( function ( $capability ) {
				return '#' . esc_attr( $capability ) . "_update";
			}, $permissions ),
			'staff_exist'         => boolval( $staff ),
			'date_of_birth_exist' => boolval( $date_of_birth ),
			'date_of_birth'       => esc_attr( $date_of_birth )
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update administrator */
	public static function update_administrator() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['administrator_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-administrator-$id"], "update-administrator-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		if ( user_can( $id, 'manage_options' ) ) {
			die();
		}

		$staff = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_staffs WHERE is_deleted = 0 AND user_id = $id AND institute_id = $institute_id" );

		$first_name       = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$last_name        = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
		$password         = isset( $_POST['password'] ) ? $_POST['password'] : '';
		$password_confirm = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';
		$permissions      = ( isset( $_POST['permissions'] ) && is_array( $_POST['permissions'] ) ) ? $_POST['permissions'] : [];

		$salary           = number_format( isset( $_POST['salary'] ) ? max( floatval( sanitize_text_field( $_POST['salary'] ) ), 0 ) : 0, 2, '.', '' );
		$job_title        = isset( $_POST['job_title'] ) ? sanitize_text_field( $_POST['job_title'] ) : '';
		$job_description  = isset( $_POST['job_description'] ) ? sanitize_textarea_field( $_POST['job_description'] ) : '';
		$gender           = isset( $_POST['gender'] ) ? sanitize_text_field( $_POST['gender'] ) : '';
		$date_of_birth    = ( isset( $_POST['date_of_birth'] ) && ! empty( $_POST['date_of_birth'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['date_of_birth'] ) ) ) : null;
		$id_proof         = ( isset( $_FILES['id_proof'] ) && is_array( $_FILES['id_proof'] ) ) ? $_FILES['id_proof'] : null;
		$id_proof_in_db   = isset( $_POST['id_proof_in_db'] ) ? intval( sanitize_text_field( $_POST['id_proof_in_db'] ) ) : null;
		$address          = isset( $_POST['address'] ) ? sanitize_textarea_field( $_POST['address'] ) : '';
		$city             = isset( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : '';
		$zip              = isset( $_POST['zip'] ) ? sanitize_text_field( $_POST['zip'] ) : '';
		$state            = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
		$nationality      = isset( $_POST['nationality'] ) ? sanitize_text_field( $_POST['nationality'] ) : '';
		$phone            = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		$qualification    = isset( $_POST['qualification'] ) ? sanitize_text_field( $_POST['qualification'] ) : '';
		$email            = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
		$photo            = ( isset( $_FILES['photo'] ) && is_array( $_FILES['photo'] ) ) ? $_FILES['photo'] : null;
		$photo_in_db      = isset( $_POST['photo_in_db'] ) ? intval( sanitize_text_field( $_POST['photo_in_db'] ) ) : null;
		$signature        = ( isset( $_FILES['signature'] ) && is_array( $_FILES['signature'] ) ) ? $_FILES['signature'] : null;
		$signature_in_db  = isset( $_POST['signature_in_db'] ) ? intval( sanitize_text_field( $_POST['signature_in_db'] ) ) : null;
		$is_active        = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;
		$add_staff_record = isset( $_POST['add_staff_record'] ) ? boolval( sanitize_text_field( $_POST['add_staff_record'] ) ) : 0;

		$errors = array();
		if ( ! empty( $password ) && ( $password !== $password_confirm ) ) {
			$errors['password_confirm'] = esc_html__( 'Please confirm password.', WL_MIM_DOMAIN );
		}

		if ( ! array_intersect( $permissions, array_keys( WL_MIM_Helper::get_capabilities() ) ) == $permissions ) {
			wp_send_json_error( esc_html__( 'Please select valid permissions.', WL_MIM_DOMAIN ) );
		}

		if ( $add_staff_record ) {
			if ( empty( $salary ) ) {
				$errors['salary'] = esc_html__( 'Please specify salary.', WL_MIM_DOMAIN );
			}

			if ( empty( $job_title ) ) {
				$errors['job_title'] = esc_html__( 'Please specify job title.', WL_MIM_DOMAIN );
			}

			if ( ! empty( $email ) && ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$errors['email'] = esc_html__( 'Please provide a valid email address.', WL_MIM_DOMAIN );
			}
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'ID'                => $id,
					'first_name'        => $first_name,
					'last_name'         => $last_name,
					'wlim_institute_id' => $institute_id
				);

				$reload = false;
				if ( ! empty( $password ) ) {
					$data['user_pass'] = $password;
					if ( get_current_user_id() == $id ) {
						$reload = true;
					}
				}

				$user_id = wp_update_user( $data );
				if ( is_wp_error( $user_id ) ) {
					wp_send_json_error( esc_html__( $user_id->get_error_message(), WL_MIM_DOMAIN ) );
				}

				$user = new WP_User( $user_id );

				if ( get_current_user_id() != $id ) {
					if ( ! user_can( $user, WL_MIM_Helper::$core_capability ) ) {
						foreach ( WL_MIM_Helper::get_capabilities() as $capability_key => $capability_value ) {
							if ( ! current_user_can( $capability_key ) ) {
								continue;
							}
							if ( in_array( $capability_key, $permissions ) ) {
								$user->add_cap( $capability_key );
							} else {
								$user->remove_cap( $capability_key );
							}
						}
					}
				}

				if ( $add_staff_record ) {
					$inactive_at = null;
					if ( ! $is_active ) {
						$inactive_at = date( 'Y-m-d H:i:s' );
					}

					$data = array(
						'first_name'      => $first_name,
						'last_name'       => $last_name,
						'salary'          => $salary,
						'job_title'       => $job_title,
						'job_description' => $job_description,
						'last_name'       => $last_name,
						'gender'          => $gender,
						'date_of_birth'   => $date_of_birth,
						'address'         => $address,
						'city'            => $city,
						'zip'             => $zip,
						'state'           => $state,
						'nationality'     => $nationality,
						'phone'           => $phone,
						'qualification'   => $qualification,
						'email'           => $email,
						'user_id'         => $user_id,
						'is_active'       => $is_active,
						'inactive_at'     => $inactive_at,
						'updated_at'      => date( 'Y-m-d H:i:s' ),
						'institute_id'    => $institute_id
					);

					if ( ! empty( $id_proof ) ) {
						$id_proof = media_handle_upload( 'id_proof', 0 );
						if ( is_wp_error( $id_proof ) ) {
							throw new Exception( esc_html__( $id_proof->get_error_message(), WL_MIM_DOMAIN ) );
						}
						$data['id_proof'] = $id_proof;
					}

					if ( ! empty( $photo ) ) {
						$photo = media_handle_upload( 'photo', 0 );
						if ( is_wp_error( $photo ) ) {
							throw new Exception( esc_html__( $photo->get_error_message(), WL_MIM_DOMAIN ) );
						}
						$data['photo_id'] = $photo;
					}

					if ( ! empty( $signature ) ) {
						$signature = media_handle_upload( 'signature', 0 );
						if ( is_wp_error( $signature ) ) {
							throw new Exception( esc_html__( $signature->get_error_message(), WL_MIM_DOMAIN ) );
						}
						$data['signature_id'] = $signature;
					}

					if ( $staff ) {
						$success = $wpdb->update( "{$wpdb->prefix}wl_min_staffs", $data, array(
							'is_deleted'   => 0,
							'id'           => $staff->id,
							'institute_id' => $institute_id
						) );
					} else {
						$data['added_by'] = get_current_user_id();
						$data['created_at'] = current_time( 'Y-m-d H:i:s' );
						$success          = $wpdb->insert( "{$wpdb->prefix}wl_min_staffs", $data );
					}

					if ( $success === false ) {
						throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
					}
				} else {
					if ( $staff ) {
						$success = $wpdb->update( "{$wpdb->prefix}wl_min_staffs",
							array(
								'is_deleted' => 1,
								'deleted_at' => date( 'Y-m-d H:i:s' )
							), array( 'is_deleted' => 0, 'id' => $staff->id, 'institute_id' => $institute_id )
						);
						if ( $success === false ) {
							throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
						}
					}
				}

				$wpdb->query( 'COMMIT;' );

				if ( ! empty( $id_proof ) && ! empty( $id_proof_in_db ) ) {
					wp_delete_attachment( $id_proof_in_db, true );
				}

				if ( ! empty( $photo ) && ! empty( $photo_in_db ) ) {
					wp_delete_attachment( $photo_in_db, true );
				}

				if ( ! empty( $signature ) && ! empty( $signature_in_db ) ) {
					wp_delete_attachment( $signature_in_db, true );
				}

				wp_send_json_success( array(
					'message' => esc_html__( 'Administrator updated successfully.', WL_MIM_DOMAIN ),
					'reload'  => $reload
				) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Get staff data to display on table */
	public static function get_staff_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_staffs WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC" );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$user     = get_user_by( 'ID', $row->user_id );
				$id       = $row->id;
				$username = '-';
				$user_id  = null;
				if ( $user ) {
					$username = $user->user_login;
					$user_id  = $user->ID;
					if ( user_can( $user_id, 'manage_options' ) ) {
						continue;
					}
				}
				$salary          = $row->salary;
				$job_title       = $row->job_title;
				$job_description = $row->job_description ? substr( $row->job_description, 0, 80 ) : '-';
				$phone           = $row->phone ? $row->phone : '-';
				$email           = $row->email ? $row->email : '-';
				$is_acitve       = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
				$date            = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
				$added_by        = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

				$results["data"][] = array(
					esc_html( $username ),
					esc_html( $salary ),
					esc_html( $job_title ),
					esc_html( $job_description ),
					esc_html( $phone ),
					esc_html( $email ),
					esc_html( $is_acitve ),
					esc_html( $added_by ),
					esc_html( $date ),
					'<a class="mr-3" href="#update-administrator" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $user_id . '"><i class="fa fa-edit"></i></a>'
				);
			}
		}

		if ( ! isset( $results["data"] ) ) {
			$results["data"] = array();
		}

		wp_send_json( $results );
	}

	/* Check permission to manage administrator */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_administrators' ) || ! $institute_id ) {
			die();
		}
	}

	/* Get user administrator data to display on table */
	public static function get_user_administrator_data() {
		self::check_user_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;

		$data           = $wpdb->get_results( "SELECT * FROM {$wpdb->base_prefix}users ORDER BY id DESC" );
		$institute_data = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}wl_min_institutes", OBJECT_K );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id                     = $row->ID;
				$first_name             = get_user_meta( $id, 'first_name', true ) ? get_user_meta( $id, 'first_name', true ) : '-';
				$last_name              = get_user_meta( $id, 'last_name', true ) ? get_user_meta( $id, 'last_name', true ) : '-';
				$username               = $row->user_login;
				$manage_multi_institute = user_can( $row->ID, WL_MIM_Helper::get_multi_institute_capability() ) ? esc_html__( 'Manage Multi Institute', WL_MIM_DOMAIN ) : '';
				$permissions            = implode( '<br>', array_intersect_key( WL_MIM_Helper::get_capabilities(), array_flip( array_intersect( array_keys( ( new WP_User( $id ) )->allcaps ), array_keys( WL_MIM_Helper::get_capabilities() ) ) ) ) );
				$added_on               = date_format( date_create( $row->user_registered ), "d-m-Y g:i A" );

				if ( $manage_multi_institute ) {
					$permissions = "<strong>$manage_multi_institute</strong><br>" . $permissions;
				}

				$current_instittue_name = '-';
				$current_institute_id   = get_user_meta( $row->ID, 'wlim_institute_id', true );
				if ( $current_institute_id && isset( $institute_data[ $current_institute_id ] ) ) {
					$current_instittue_name = $institute_data[ $current_institute_id ]->name;
				}

				$results["data"][] = array(
					$first_name,
					$last_name,
					$username,
					$permissions,
					$current_instittue_name,
					$added_on,
					'<a class="mr-3" href="#update-user-administrator" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Add new user administrator */
	public static function add_user_administrator() {
		self::check_user_permission();
		if ( ! wp_verify_nonce( $_POST['add-user-administrator'], 'add-user-administrator' ) ) {
			die();
		}
		global $wpdb;

		$first_name       = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$last_name        = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
		$username         = isset( $_POST['username'] ) ? sanitize_text_field( $_POST['username'] ) : '';
		$password         = isset( $_POST['password'] ) ? $_POST['password'] : '';
		$password_confirm = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';
		$permissions      = ( isset( $_POST['permissions'] ) && is_array( $_POST['permissions'] ) ) ? $_POST['permissions'] : [];

		$institute_id = intval( sanitize_text_field( $_POST['institute'] ) );

		$manage_multi_institute = isset( $_POST['manage_multi_institute'] ) ? boolval( sanitize_text_field( $_POST['manage_multi_institute'] ) ) : 0;

		$errors = array();
		if ( empty( $username ) ) {
			$errors['username'] = esc_html__( 'Please provide username.', WL_MIM_DOMAIN );
		}

		if ( empty( $password ) ) {
			$errors['password'] = esc_html__( 'Please provide password.', WL_MIM_DOMAIN );
		}

		if ( empty( $password_confirm ) ) {
			$errors['password_confirm'] = esc_html__( 'Please confirm password.', WL_MIM_DOMAIN );
		}

		if ( $password !== $password_confirm ) {
			$errors['password'] = esc_html__( 'Passwords do not match.', WL_MIM_DOMAIN );
		}

		if ( ! $manage_multi_institute ) {
			$institute = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id" );
			if ( ! $institute ) {
				$errors['institute'] = esc_html__( 'Please select institute.', WL_MIM_DOMAIN );
			}

			if ( ! array_intersect( $permissions, array_keys( WL_MIM_Helper::get_capabilities() ) ) == $permissions ) {
				wp_send_json_error( esc_html__( 'Please select valid permissions.', WL_MIM_DOMAIN ) );
			}
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'first_name' => $first_name,
					'last_name'  => $last_name,
					'user_login' => $username,
					'user_pass'  => $password
				);

				$user_id = wp_insert_user( $data );
				if ( is_wp_error( $user_id ) ) {
					wp_send_json_error( esc_html__( $user_id->get_error_message(), WL_MIM_DOMAIN ) );
				}

				$user = new WP_User( $user_id );

				$institute_permissions = array_keys( WL_MIM_Helper::get_capabilities() );
				array_push( $institute_permissions, WL_MIM_Helper::get_multi_institute_capability() );

				if ( ! $manage_multi_institute ) {
					foreach ( $institute_permissions as $capability ) {
						$user->remove_cap( $capability );
					}
					foreach ( $permissions as $capability ) {
						$user->add_cap( $capability );
					}
					update_user_meta( $user_id, 'wlim_institute_id', $institute_id );
				} else {
					foreach ( $institute_permissions as $capability ) {
						$user->add_cap( $capability );
					}
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Administrator added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch user administrator to update */
	public static function fetch_user_administrator() {
		self::check_user_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->base_prefix}users WHERE ID = $id" );
		if ( ! $row ) {
			die();
		}
		$manage_multi_institute = false;
		if ( user_can( $row->ID, WL_MIM_Helper::get_multi_institute_capability() ) ) {
			$manage_multi_institute = true;
		}
		$wlim_active_institutes = WL_MIM_Helper::get_institutes();
		$institute_id           = get_user_meta( $row->ID, 'wlim_institute_id', true );
		?><?php $nonce = wp_create_nonce( "update-user-administrator-$id" );
		ob_start(); ?>
        <input type="hidden" name="update-user-administrator-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action" value="wl-mim-update-user-administrator">
        <div class="row">
            <div class="col form-group">
                <label for="wlim-administrator-first_name_update" class="col-form-label"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?>:</label>
                <input name="first_name" type="text" class="form-control" id="wlim-administrator-first_name_update" placeholder="<?php esc_html_e( "First Name", WL_MIM_DOMAIN ); ?>" value="<?php echo get_user_meta( $id, 'first_name', true ); ?>">
            </div>
            <div class="col form-group">
                <label for="wlim-administrator-last_name_update" class="col-form-label"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?>:</label>
                <input name="last_name" type="text" class="form-control" id="wlim-administrator-last_name_update" placeholder="<?php esc_html_e( "Last Name", WL_MIM_DOMAIN ); ?>" value="<?php echo get_user_meta( $id, 'last_name', true ); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="wlim-administrator-username_update" class="col-form-label"><?php esc_html_e( 'Username', WL_MIM_DOMAIN ); ?>:
                <small class="text-secondary"><em><?php esc_html_e( "cannot be changed.", WL_MIM_DOMAIN ); ?></em></small>
            </label>
            <input name="username" type="text" class="form-control" id="wlim-administrator-username_update" placeholder="<?php esc_html_e( "Username", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->user_login ); ?>" disabled>
        </div>
        <div class="form-group">
            <label for="wlim-administrator-password_update" class="col-form-label"><?php esc_html_e( 'Password', WL_MIM_DOMAIN ); ?>:</label>
            <input name="password" type="password" class="form-control" id="wlim-administrator-password_update" placeholder="<?php esc_html_e( "Password", WL_MIM_DOMAIN ); ?>">
        </div>
        <div class="form-group">
            <label for="wlim-administrator-password_confirm_update" class="col-form-label"><?php esc_html_e( 'Confirm Password', WL_MIM_DOMAIN ); ?>:</label>
            <input name="password_confirm" type="password" class="form-control" id="wlim-administrator-password_confirm_update" placeholder="<?php esc_html_e( "Confirm Password", WL_MIM_DOMAIN ); ?>">
        </div>

        <hr>
        <div class="form-check pl-0">
            <input name="manage_multi_institute" class="position-static mt-0 form-check-input wlim-manage_multi_institute" type="checkbox" id="wlim-manage_multi_institute_update" <?php checked( $manage_multi_institute, true, true ); ?>>
            <label class="form-check-label" for="wlim-manage_multi_institute_update"><?php esc_html_e( 'Manage Multi Institute', WL_MIM_DOMAIN ); ?></label>
        </div>

        <div class="wlim-manage-single-institute">
            <hr>
            <div class="form-group">
                <label for="wlim-administrator-institute_update" class="col-form-label"><?php esc_html_e( "Manage Single Institute", WL_MIM_DOMAIN ); ?>:</label>
                <select name="institute" class="form-control selectpicker" id="wlim-administrator-institute_update">
                    <option value="">-------- <?php esc_html_e( "Select Institute", WL_MIM_DOMAIN ); ?>--------</option>
					<?php
					if ( count( $wlim_active_institutes ) > 0 ) {
						foreach ( $wlim_active_institutes as $active_institute ) { ?>
                            <option value="<?php echo esc_attr( $active_institute->id ); ?>" <?php selected( $institute_id, $active_institute->id, true ); ?>><?php echo esc_html( $active_institute->name ); ?></option>
							<?php
						}
					} ?>
                </select>
            </div>

            <label class="col-form-label"><?php esc_html_e( 'Permissions', WL_MIM_DOMAIN ); ?>:
				<?php
				if ( user_can( $row->ID, WL_MIM_Helper::$core_capability ) ) { ?>
                    <small class="text-secondary">
                        <em><?php esc_html_e( "cannot be changed for users with role 'Administrator'.", WL_MIM_DOMAIN ); ?></em>
                    </small>
					<?php
				} ?>
            </label>
			<?php
			foreach ( WL_MIM_Helper::get_capabilities() as $capability_key => $capability_value ) { ?>
                <div class="form-check pl-0">
                    <input name="permissions[]" class="position-static mt-0 form-check-input" type="checkbox" id="<?php echo esc_attr( $capability_key ) . "_update"; ?>" value="<?php echo esc_attr( $capability_key ); ?>" <?php echo user_can( $row->ID, WL_MIM_Helper::$core_capability ) ? 'disabled' : '' ?>>
                    <label class="form-check-label" for="<?php echo esc_attr( $capability_key ) . "_update"; ?>"><?php esc_html_e( $capability_value, WL_MIM_DOMAIN ); ?></label>
                </div>
				<?php
			} ?>
        </div><input type="hidden" name="administrator_id" value="<?php echo esc_attr( $row->ID ); ?>">
		<?php
		$html        = ob_get_clean();
		$permissions = array_intersect( array_keys( ( new WP_User( $id ) )->allcaps ), array_keys( WL_MIM_Helper::get_capabilities() ) );

		$json = json_encode( array(
			'manage_multi_institute' => boolval( $manage_multi_institute ),
			'permissions'            => array_map( function ( $capability ) {
				return '#' . esc_attr( $capability ) . "_update";
			}, $permissions ),
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update user administrator */
	public static function update_user_administrator() {
		self::check_user_permission();
		$id = intval( sanitize_text_field( $_POST['administrator_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-user-administrator-$id"], "update-user-administrator-$id" ) ) {
			die();
		}
		global $wpdb;

		$first_name       = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$last_name        = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
		$password         = isset( $_POST['password'] ) ? $_POST['password'] : '';
		$password_confirm = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';
		$permissions      = ( isset( $_POST['permissions'] ) && is_array( $_POST['permissions'] ) ) ? $_POST['permissions'] : [];

		$institute_id = intval( sanitize_text_field( $_POST['institute'] ) );

		$manage_multi_institute = isset( $_POST['manage_multi_institute'] ) ? boolval( sanitize_text_field( $_POST['manage_multi_institute'] ) ) : 0;

		$errors = array();
		if ( ! empty( $password ) && ( $password !== $password_confirm ) ) {
			$errors['password_confirm'] = esc_html__( 'Please confirm password.', WL_MIM_DOMAIN );
		}

		if ( ! $manage_multi_institute ) {
			if ( $institute_id || count( $permissions ) > 0 ) {
				$institute = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id" );
				if ( ! $institute ) {
					$errors['institute'] = esc_html__( 'Please select institute.', WL_MIM_DOMAIN );
				}
			}

			if ( ! array_intersect( $permissions, array_keys( WL_MIM_Helper::get_capabilities() ) ) == $permissions ) {
				wp_send_json_error( esc_html__( 'Please select valid permissions.', WL_MIM_DOMAIN ) );
			}
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'ID'         => $id,
					'first_name' => $first_name,
					'last_name'  => $last_name
				);

				$reload = false;
				if ( ! empty( $password ) ) {
					$data['user_pass'] = $password;
					if ( get_current_user_id() == $id ) {
						$reload = true;
					}
				}

				$user_id = wp_update_user( $data );
				if ( is_wp_error( $user_id ) ) {
					wp_send_json_error( esc_html__( $user_id->get_error_message(), WL_MIM_DOMAIN ) );
				}

				$user = new WP_User( $user_id );

				$institute_permissions = array_keys( WL_MIM_Helper::get_capabilities() );
				array_push( $institute_permissions, WL_MIM_Helper::get_multi_institute_capability() );

				if ( ! $manage_multi_institute ) {
					foreach ( $institute_permissions as $capability ) {
						$user->remove_cap( $capability );
					}
					if ( ! user_can( $user, WL_MIM_Helper::$core_capability ) ) {
						foreach ( WL_MIM_Helper::get_capabilities() as $capability_key => $capability_value ) {
							if ( in_array( $capability_key, $permissions ) ) {
								$user->add_cap( $capability_key );
							} else {
								$user->remove_cap( $capability_key );
							}
						}
					}
					if ( $institute_id ) {
						update_user_meta( $user_id, 'wlim_institute_id', $institute_id );
					} else {
						delete_user_meta( $user_id, 'wlim_institute_id' );
					}
				} else {
					foreach ( $institute_permissions as $capability ) {
						$user->add_cap( $capability );
					}
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array(
					'message' => esc_html__( 'Administrator updated successfully.', WL_MIM_DOMAIN ),
					'reload'  => $reload
				) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Check permission to manage user administrator */
	private static function check_user_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die();
		}
	}
}
?>