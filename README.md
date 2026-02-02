
# Expense Tracker

Modern full-stack Expense Tracker (PHP + MySQL + HTML/CSS/JS).

## Features
- Dashboard with charts and budget summary
- Add, edit, delete, and filter expenses
- CSV export for expenses
- Profile page with image upload and password change
- Dark/Light mode with theme toggle (sidebar, always visible)
- Responsive, mobile-first UI
- Sidebar profile box position: top on desktop, bottom on mobile
- Font Awesome icons throughout
- Help & Support page (messages stored in database)

## Setup
1. Import `db.sql` into your MySQL database (creates all tables, including `support_messages`).
2. Update `config.php` with your database credentials.
3. Ensure `uploads/profile_pics` is writable by the web server.
4. Run with Apache or: `php -S localhost:8000` from the project folder.

## Pages
- `dashboard.php` — Overview and charts
- `expenses.php` — List, filter, export expenses
- `add_expense.php` / `edit_expense.php` — Add or edit an expense
- `profile.php` — Update profile, upload photo, change password
- `help.php` — Contact support (messages saved in DB)

## UI/UX Notes
- Sidebar profile box is at the top on desktop, moves to the bottom on mobile for better usability.
- Theme toggle (moon/sun) is always visible in the sidebar/profile box.
- Table and card backgrounds adapt to dark mode, with no borders in dark tables for a clean look.
- All navigation and actions are mobile-friendly.

## Support Messages
All messages sent from the Help & Support page are stored in the `support_messages` table for admin review.

---
For any issues, use the Help & Support page in the app!
