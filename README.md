# Bayani Quiz

An educational quiz game about Philippine history, heroes, events, and culture. Built for Filipino students and curious adults to test their knowledge in a fun, engaging way.

## Screenshots

[Add screenshots here]

## Tech Stack

- **Backend:** PHP 8.2 (procedural style)
- **Database:** MySQL 5.7+
- **Styling:** Tailwind CSS via CDN
- **JavaScript:** Vanilla JS
- **Icons:** Font Awesome 6
- **Fonts:** Google Fonts (Poppins, Playfair Display)

## Features

- 5 quiz categories covering different eras of Philippine history
- 50+ educational questions with fun facts
- Countdown timer for each question
- Score tracking and leaderboard
- Shareable score cards via Canvas API
- Mobile-responsive design
- Philippine flag-inspired color scheme

## Setup Instructions

1. Clone the repository
2. Create a MySQL database
3. Update `includes/db.php` with your database credentials
4. Upload files to your web server via FTP
5. Visit `yourdomain.com/admin/seed.php?token=YOUR_SECRET` to seed the database
6. Delete or disable `seed.php` after seeding
7. Test all pages on mobile and desktop

## Deployment on InfinityFree

1. Create a free account at infinityfree.com
2. Create a MySQL database in the control panel
3. Update `includes/db.php` with the provided credentials
4. Connect via FTP using FileZilla (credentials from InfinityFree)
5. Upload all files to the `htdocs/` folder
6. Visit `yourdomain.great-site.net/admin/seed.php?token=YOUR_SECRET` to seed the DB
7. Delete or disable `seed.php` after seeding
8. Test all pages on mobile and desktop

## License

MIT
