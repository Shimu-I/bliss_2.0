
# Daycare Management System Documentation

## Overview
The Daycare Management System is a web-based application designed to streamline daycare operations, enhance communication, and ensure high-quality care. Built using **PHP** (backend) and **MySQL** (database), with a frontend using **HTML**, **CSS**, and **JavaScript** (with **Bootstrap** for responsive design), the system supports role-based access for Parents, Caregivers, and Admins. This documentation details the implementation of each feature, including database interactions, PHP logic, and frontend interfaces.

## Technology Stack
- **Backend:** PHP 8.x (for server-side logic, API endpoints, and database interactions)
- **Database:** MySQL 8.0.42 (for data storage and management)
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5 (for responsive UI)
- **Additional Libraries:** jQuery (for AJAX), PHPMailer (for email notifications), and Twilio SDK (for SMS notifications, optional)

## Database Schema
The MySQL database (`daycare_management`) includes the following key tables (as provided, with suggested additions):
- `users`: Stores user details (Parents, Caregivers, Admins) with role-based access.
- `roles`: Defines roles (Parent, Caregiver, Admin) for RBAC.
- `children`: Stores child information linked to parents.
- `caregiver_profiles`: Stores caregiver qualifications and work history.
- `caregiver_availability`: Tracks caregiver schedules.
- `caregiver_child_assignments`: Links caregivers to children.
- `attendance`: Tracks child check-in/check-out times.
- `activities`: Logs daily activities and milestones.
- `meals`: Manages meal schedules and dietary restrictions.
- `special_care_plans`: Stores care plans for children with special needs.
- `invoices`, `billing_items`, `payments`: Handle billing and payment tracking.
- `notifications`: Manages real-time notifications.
- `audit_logs`: Tracks system actions for transparency.
- **Suggested Additions**:
  - `caregiver_applications`: Tracks caregiver hiring process.
  - `meal_schedules`: Stores planned weekly menus.
  - `learning_plans`: Tracks developmental milestones and learning goals.
  - `permissions`: Defines granular RBAC permissions.

## Feature Documentation

### 1. Profile Creation
**Description**: Allows secure, role-based profile creation for Parents, Caregivers, and Admins, with interfaces to view/edit profiles and manage child-related data.

**Implementation**:
- **Database**:
  - `users`: Stores `user_id`, `role_id`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `preferred_language`.
  - `roles`: Defines roles (`Parent`, `Caregiver`, `Admin`).
  - `caregiver_profiles`: Stores caregiver-specific data (`qualifications`, `work_history`, `special_certifications`).
  - `children`: Links children to parents via `parent_id`.
  - `notifications`: Sends profile update notifications (`notification_type = 'Profile Update'`).
- **PHP Logic**:
  - **User Registration** (`register.php`):
    - Validates input (e.g., unique email, strong password).
    - Hashes password using `password_hash()`.
    - Inserts user into `users` table and assigns `role_id`.
    - For Caregivers, inserts qualifications into `caregiver_profiles`.
    - Example:
      ```php
      $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
      $sql = "INSERT INTO users (role_id, first_name, last_name, email, phone, password_hash) 
              VALUES (?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("isssss", $_POST['role_id'], $_POST['first_name'], $_POST['last_name'], 
                        $_POST['email'], $_POST['phone'], $password_hash);
      $stmt->execute();
      ```
  - **Child Profile Management** (`child_profile.php`):
    - Parents add children to `children` table, linked via `parent_id`.
    - Admins can view/edit all child profiles.
    - Example:
      ```php
      $sql = "INSERT INTO children (first_name, last_name, date_of_birth, parent_id, emergency_contact_name, emergency_contact_phone) 
              VALUES (?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sssiss", $_POST['first_name'], $_POST['last_name'], $_POST['dob'], 
                        $user_id, $_POST['emergency_name'], $_POST['emergency_phone']);
      $stmt->execute();
      ```
  - **RBAC**:
    - Role-based access enforced via session variables (`$_SESSION['role_id']`).
    - Example: Restrict admin pages:
      ```php
      if ($_SESSION['role_id'] != 3) {
          header("Location: unauthorized.php");
          exit;
      }
      ```
  - **Notifications**:
    - Uses PHPMailer for email and Twilio for SMS to notify users of profile updates.
    - Inserts notification into `notifications` table.
    - Example:
      ```php
      $sql = "INSERT INTO notifications (user_id, message, notification_type, channel) 
              VALUES (?, ?, 'Profile Update', 'Email')";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("is", $user_id, $message);
      $stmt->execute();
      ```
- **Frontend**:
  - Forms for user registration and child profile creation (Bootstrap).
  - Caregiver profile view/edit interface for Admins and Caregivers.
  - Parent dashboard to view/update child data.
  - Example (HTML form for user registration):
    ```html
    <form action="register.php" method="POST">
        <input type="text" name="first_name" class="form-control" required>
        <input type="email" name="email" class="form-control" required>
        <select name="role_id" class="form-control">
            <option value="1">Parent</option>
            <option value="2">Caregiver</option>
            <option value="3">Admin</option>
        </select>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    ```

**Tasks Completed**:
- User profile creation for all roles.
- Caregiver profile with qualifications and work history.
- Child data management linked to parents.
- RBAC for secure access.
- Notification system for profile updates.

### 2. Caregiver Hiring Process
**Description**: Manages caregiver applications, qualification verification, and matching based on children’s needs.

**Implementation**:
- **Database**:
  - `caregiver_profiles`: Stores qualifications and certifications.
  - `caregiver_availability`: Tracks available days/times.
  - `caregiver_child_assignments`: Links caregivers to children.
  - **New Table**: `caregiver_applications` (`application_id`, `caregiver_id`, `status`, `interview_date`, `admin_id`).
  - `notifications`: Notifies parents of caregiver assignments.
- **PHP Logic**:
  - **Application Submission** (`apply_caregiver.php`):
    - Caregivers upload qualifications and work history, stored in `caregiver_profiles`.
    - Application status (`Pending`, `Approved`, `Rejected`) stored in `caregiver_applications`.
    - Example:
      ```php
      $sql = "INSERT INTO caregiver_applications (caregiver_id, status) VALUES (?, 'Pending')";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $caregiver_id);
      $stmt->execute();
      ```
  - **Qualification Verification** (`verify_caregiver.php`):
    - Admins review applications and update `status` in `caregiver_applications`.
    - Example:
      ```php
      $sql = "UPDATE caregiver_applications SET status = ?, admin_id = ? WHERE application_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sii", $_POST['status'], $admin_id, $_POST['application_id']);
      $stmt->execute();
      ```
  - **Caregiver Matching** (`assign_caregiver.php`):
    - Matches caregivers to children based on `special_care_plans` and `caregiver_profiles.special_certifications`.
    - Inserts into `caregiver_child_assignments`.
    - Example:
      ```php
      $sql = "SELECT c.caregiver_id FROM caregiver_profiles c 
              WHERE c.special_certifications LIKE ? AND c.caregiver_id IN 
              (SELECT caregiver_id FROM caregiver_availability WHERE day_of_week = ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ss", $special_needs, $day);
      $stmt->execute();
      ```
  - **Parent Approval**:
    - Parents receive notifications to approve caregivers via `notifications` table.
    - Example:
      ```php
      $sql = "INSERT INTO notifications (user_id, message, notification_type, channel) 
              VALUES (?, ?, 'Other', 'Email')";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("is", $parent_id, "New caregiver assigned: $caregiver_name");
      $stmt->execute();
      ```
- **Frontend**:
  - Caregiver application form for uploading qualifications.
  - Admin dashboard to review/approve applications.
  - Parent portal to view/approve assigned caregivers.

**Tasks Completed**:
- Caregiver application and qualification storage.
- Verification and approval process.
- Matching system based on skills and availability.
- Notification system for parents.

### 3. Pick-Up & Drop-Off Management
**Description**: Tracks child attendance with check-in/check-out times and notifies parents.

**Implementation**:
- **Database**:
  - `attendance`: Stores `check_in_time`, `check_out_time`, `child_id`, `date`.
  - `notifications`: Sends attendance alerts (`notification_type = 'Attendance'`).
- **PHP Logic**:
  - **Check-In/Check-Out** (`attendance.php`):
    - Records check-in/out times in `attendance` table.
    - Example:
      ```php
      $sql = "INSERT INTO attendance (child_id, check_in_time, date) VALUES (?, NOW(), ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("is", $child_id, $date);
      $stmt->execute();
      ```
  - **Real-Time Notifications**:
    - Sends SMS/email on check-in/out using Twilio/PHPMailer.
    - Example:
      ```php
      $sql = "INSERT INTO notifications (user_id, message, notification_type, channel) 
              VALUES (?, ?, 'Attendance', 'SMS')";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("is", $parent_id, "Child checked in at $time");
      $stmt->execute();
      ```
  - **Absentee Alerts** (`cron_absentee.php`):
    - Cron job checks for missing check-ins by a set time (e.g., 9 AM).
    - Example:
      ```php
      $sql = "SELECT c.child_id, c.parent_id FROM children c 
              WHERE c.child_id NOT IN (SELECT child_id FROM attendance WHERE date = ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("s", $today);
      $stmt->execute();
      ```
  - **Attendance History**:
    - Exports attendance data for analysis/billing.
    - Example:
      ```php
      $sql = "SELECT * FROM attendance WHERE child_id = ? AND date BETWEEN ? AND ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("iss", $child_id, $start_date, $end_date);
      $stmt->execute();
      ```
- **Frontend**:
  - Caregiver interface for check-in/check-out.
  - Parent dashboard to view attendance history.
  - Export button for attendance reports (CSV).

**Tasks Completed**:
- Check-in/check-out system.
- Real-time notifications.
- Absentee alerts.
- Attendance history and export.

### 4. Special Child Care Support
**Description**: Manages tailored care plans for children with special needs, including caregiver matching and progress tracking.

**Implementation**:
- **Database**:
  - `special_care_plans`: Stores `medical_conditions`, `developmental_needs`, `physical_needs`, `medication_schedule`, `therapy_requirements`, `activity_preferences`.
  - `caregiver_child_assignments`: Links trained caregivers to children.
- **PHP Logic**:
  - **Care Plan Creation** (`care_plan.php`):
    - Parents input special needs, stored in `special_care_plans`.
    - Example:
      ```php
      $sql = "INSERT INTO special_care_plans (child_id, medical_conditions, developmental_needs) 
              VALUES (?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("iss", $child_id, $_POST['medical_conditions'], $_POST['developmental_needs']);
      $stmt->execute();
      ```
  - **Caregiver Matching**:
    - Matches caregivers based on `special_certifications` and `special_care_plans`.
    - Example:
      ```php
      $sql = "SELECT c.caregiver_id FROM caregiver_profiles c 
              WHERE c.special_certifications LIKE ? AND c.caregiver_id IN 
              (SELECT caregiver_id FROM caregiver_child_assignments WHERE child_id = ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("si", $special_needs, $child_id);
      $stmt->execute();
      ```
  - **Progress Tracking**:
    - Logs progress in `activities` table (`activity_type = 'Therapy'` or `'Milestone'`).
    - Example:
      ```php
      $sql = "INSERT INTO activities (child_id, activity_date, activity_type, description, caregiver_id) 
              VALUES (?, NOW(), 'Milestone', ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("isi", $child_id, $description, $caregiver_id);
      $stmt->execute();
      ```
- **Frontend**:
  - Parent form to input/update special care plans.
  - Caregiver interface to view care plans and log progress.
  - Parent dashboard to view care plan details and progress.

**Tasks Completed**:
- Special care plan creation.
- Caregiver matching for special needs.
- Progress tracking via activities.

### 5. Meal Planning
**Description**: Manages meal schedules, dietary restrictions, and parent feedback, integrated with billing.

**Implementation**:
- **Database**:
  - `meals`: Stores `child_id`, `meal_date`, `meal_type`, `dietary_restrictions`, `feedback`.
  - **New Table**: `meal_schedules` (`schedule_id`, `child_id`, `meal_date`, `meal_type`, `planned_menu`).
  - `billing_items`: Links meal costs to invoices.
- **PHP Logic**:
  - **Meal Scheduling** (`meal_schedule.php`):
    - Admins create weekly meal plans in `meal_schedules`.
    - Example:
      ```php
      $sql = "INSERT INTO meal_schedules (child_id, meal_date, meal_type, planned_menu) 
              VALUES (?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("isss", $child_id, $meal_date, $meal_type, $planned_menu);
      $stmt->execute();
      ```
  - **Meal Logging** (`log_meal.php`):
    - Caregivers log meals served in `meals` table.
    - Example:
      ```php
      $sql = "INSERT INTO meals (child_id, meal_date, meal_type, dietary_restrictions, feedback) 
              VALUES (?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("issss", $child_id, $meal_date, $meal_type, $restrictions, $feedback);
      $stmt->execute();
      ```
  - **Billing Integration**:
    - Adds meal costs to `billing_items`.
    - Example:
      ```php
      $sql = "INSERT INTO billing_items (invoice_id, description, amount) 
              VALUES (?, 'Meal plan', ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("id", $invoice_id, $meal_cost);
      $stmt->execute();
      ```
- **Frontend**:
  - Admin interface to plan weekly menus.
  - Caregiver form to log meals and feedback.
  - Parent portal to view meal history and provide feedback.

**Tasks Completed**:
- Meal scheduling and dietary restriction management.
- Parent feedback system.
- Meal history logging.
- Billing integration.

### 6. Day Care Diary
**Description**: Logs daily activities and milestones, providing parents with real-time updates.

**Implementation**:
- **Database**:
  - `activities`: Stores `child_id`, `activity_date`, `activity_type`, `description`, `caregiver_id`.
  - `notifications`: Sends activity updates (`notification_type = 'Activity'`).
- **PHP Logic**:
  - **Activity Logging** (`log_activity.php`):
    - Caregivers log activities in `activities` table.
    - Example:
      ```php
      $sql = "INSERT INTO activities (child_id, activity_date, activity_type, description, caregiver_id) 
              VALUES (?, NOW(), ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("issi", $child_id, $activity_type, $description, $caregiver_id);
      $stmt->execute();
      ```
  - **Real-Time Notifications**:
    - Sends updates to parents via `notifications` table.
    - Example:
      ```php
      $sql = "INSERT INTO notifications (user_id, message, notification_type, channel) 
              VALUES (?, ?, 'Activity', 'Push')";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("is", $parent_id, $message);
      $stmt->execute();
      ```
  - **Timeline View**:
    - Queries `activities` for parent dashboard.
    - Example:
      ```php
      $sql = "SELECT activity_date, activity_type, description FROM activities 
              WHERE child_id = ? ORDER BY activity_date DESC";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $child_id);
      $stmt->execute();
      ```
- **Frontend**:
  - Caregiver form to log activities.
  - Parent timeline view (Bootstrap timeline component).
  - Push notification integration for real-time updates.

**Tasks Completed**:
- Activity logging system.
- Real-time parent notifications.
- Timeline view for parents.
- Activity history.

### 7. Learning Journey
**Description**: Tracks developmental milestones and educational progress, with customizable learning plans.

**Implementation**:
- **Database**:
  - `activities`: Tracks milestones (`activity_type = 'Milestone'`).
  - **New Table**: `learning_plans` (`plan_id`, `child_id`, `milestone_goals`, `progress_notes`, `last_updated_by`).
- **PHP Logic**:
  - **Milestone Tracking** (`log_milestone.php`):
    - Caregivers log milestones in `activities`.
    - Example:
      ```php
      $sql = "INSERT INTO activities (child_id, activity_date, activity_type, description, caregiver_id) 
              VALUES (?, NOW(), 'Milestone', ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("isi", $child_id, $description, $caregiver_id);
      $stmt->execute();
      ```
  - **Learning Plan Customization** (`learning_plan.php`):
    - Parents/caregivers update `learning_plans`.
    - Example:
      ```php
      $sql = "INSERT INTO learning_plans (child_id, milestone_goals, progress_notes, last_updated_by) 
              VALUES (?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("issi", $child_id, $goals, $notes, $user_id);
      $stmt->execute();
      ```
  - **Parent Portal**:
    - Displays milestones and learning plans.
    - Example:
      ```php
      $sql = "SELECT a.description, a.activity_date, l.milestone_goals 
              FROM activities a LEFT JOIN learning_plans l ON a.child_id = l.child_id 
              WHERE a.child_id = ? AND a.activity_type = 'Milestone'";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $child_id);
      $stmt->execute();
      ```
- **Frontend**:
  - Parent portal to view milestones and learning plans.
  - Form for parents/caregivers to update learning plans.

**Tasks Completed**:
- Milestone tracking.
- Parent portal for progress viewing.
- Customizable learning plans.

### 8. Bill & Payment
**Description**: Automates invoicing, payment tracking, and provides a parent payment portal.

**Implementation**:
- **Database**:
  - `invoices`: Stores `parent_id`, `child_id`, `amount`, `status`, `issue_date`, `due_date`.
  - `billing_items`: Details invoice components.
  - `payments`: Tracks payment details.
  - `notifications`: Sends payment reminders (`notification_type = 'Payment'`).
- **PHP Logic**:
  - **Invoice Generation** (`generate_invoice.php`):
    - Creates invoices based on attendance and services.
    - Example:
      ```php
      $sql = "INSERT INTO invoices (parent_id, child_id, issue_date, due_date, amount, status) 
              VALUES (?, ?, ?, ?, ?, 'Pending')";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("iissd", $parent_id, $child_id, $issue_date, $due_date, $amount);
      $stmt->execute();
      ```
  - **Payment Processing** (`process_payment.php`):
    - Integrates with payment gateways (e.g., Stripe API) and logs payments.
    - Example:
      ```php
      $sql = "INSERT INTO payments (invoice_id, parent_id, payment_date, amount, payment_method) 
              VALUES (?, ?, NOW(), ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("iids", $invoice_id, $parent_id, $amount, $payment_method);
      $stmt->execute();
      ```
  - **Parent Portal**:
    - Displays invoices and payment history.
    - Example:
      ```php
      $sql = "SELECT i.invoice_id, i.amount, i.status, p.payment_date 
              FROM invoices i LEFT JOIN payments p ON i.invoice_id = p.invoice_id 
              WHERE i.parent_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $parent_id);
      $stmt->execute();
      ```
  - **Financial Reports** (`financial_report.php`):
    - Admins export payment summaries.
    - Example:
      ```php
      $sql = "SELECT i.parent_id, i.amount, i.status, p.payment_date 
              FROM invoices i LEFT JOIN payments p ON i.invoice_id = p.invoice_id 
              WHERE i.issue_date BETWEEN ? AND ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ss", $start_date, $end_date);
      $stmt->execute();
      ```
- **Frontend**:
  - Parent payment portal with invoice view and payment options.
  - Admin dashboard for financial reports (exportable as CSV).
  - Payment integration with Stripe for credit card/PayPal.

**Tasks Completed**:
- Invoice generation.
- Payment tracking and processing.
- Parent payment portal.
- Admin financial reports.

## Additional Notes
- **Security**:
  - Passwords hashed using `password_hash()` (BCRYPT).
  - SQL injection prevented using prepared statements (`mysqli_prepare`).
  - Session management with `$_SESSION` for RBAC.
  - HTTPS enforced for secure data transmission.
- **Scalability**:
  - Database indexes (e.g., `idx_child_activity_date`, `idx_child_date`) ensure efficient queries.
  - Normalized schema reduces redundancy.
- **Notifications**:
  - PHPMailer for email notifications.
  - Twilio SDK for SMS (optional, configurable).
  - Push notifications via JavaScript Service Workers (optional).
- **Frontend**:
  - Bootstrap 5 for responsive design.
  - jQuery AJAX for real-time updates (e.g., activity logging, notifications).
- **Cron Jobs**:
  - Daily job for absentee alerts (`cron_absentee.php`).
  - Weekly job for invoice generation (`cron_invoice.php`).

## Deployment
- **Server Requirements**: PHP 8.x, MySQL 8.x, Apache/Nginx.
- **Dependencies**: Composer for PHPMailer, Twilio SDK, Stripe PHP SDK.
- **Setup**:
  1. Import MySQL schema (`daycare_management.sql`).
  2. Configure `config.php` with database credentials and API keys (PHPMailer, Twilio, Stripe).
  3. Deploy PHP files to web server.
  4. Set up cron jobs for automated tasks.
  5. **Run the application**:
     - Open a terminal in the project directory and run:
       ```bash
       php -S localhost:8000

## Future Enhancements
- Add `media_url` to `activities` for photo/video uploads.
- Implement `care_plan_progress` table for detailed special care tracking.
- Add multi-language support using `preferred_language` in `users`.
- Integrate real-time chat for parent-caregiver communication.