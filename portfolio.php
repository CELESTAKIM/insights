<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user email to check if it's celestakim018@gmail.com
$user_query = "SELECT email FROM users WHERE id = ?";
$stmt = mysqli_prepare($db, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);
$user_email = $user['email'];
mysqli_stmt_close($stmt);

// Upload logic for work samples (unchanged)
if (isset($_POST['upload_work'])) {
    $work_dir = 'uploads/works/';
    if (!file_exists($work_dir)) mkdir($work_dir, 0777, true);
    $work_path = null;
    $error = '';

    if (!empty($_FILES['work']['name'])) {
        $work = $_FILES['work'];
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($work['type'], $allowed_types)) {
            $error = "Work sample must be a PDF, JPG, PNG, or GIF.";
        } else {
            $ext = pathinfo($work['name'], PATHINFO_EXTENSION);
            $work_path = $work_dir . $user_id . '_' . time() . '.' . $ext;
            move_uploaded_file($work['tmp_name'], $work_path);
        }
    } else {
        $error = "Please select a work sample.";
    }

    if (!$error && $work_path) {
        $query = "INSERT INTO portfolio (user_id, work_path) VALUES (?, ?)";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "is", $user_id, $work_path);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } elseif ($error) {
        echo "<p style='color: #d32f2f; text-align: center; font-size: 1em; margin: 15px 0;'>$error</p>";
    }
}

// Partnership request logic (unchanged)
if (isset($_POST['submit_partnership'])) {
    $company_name = trim($_POST['company_name']);
    $contact_email = trim($_POST['contact_email']);
    $message = trim($_POST['message']);

    if (empty($company_name) || empty($contact_email) || !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $partnership_error = "Company name and valid contact email are required.";
    } else {
        $query = "INSERT INTO partnership_requests (company_name, contact_email, message) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "sss", $company_name, $contact_email, $message);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $partnership_success = "Partnership request submitted successfully!";
    }
}

// Event handling: Add new event (only for celestakim018@gmail.com)
if (isset($_POST['add_event']) && $user_email === 'celestakim018@gmail.com') {
    $title = trim($_POST['event_title']);
    $date = trim($_POST['event_date']);
    $description = trim($_POST['event_description']);

    if (!empty($title) && !empty($date)) {
        $query = "INSERT INTO events (user_id, title, event_date, description) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $title, $date, $description);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Event handling: Update existing event (only for celestakim018@gmail.com)
if (isset($_POST['update_event']) && $user_email === 'celestakim018@gmail.com') {
    $event_id = $_POST['event_id'];
    $title = trim($_POST['event_title']);
    $date = trim($_POST['event_date']);
    $description = trim($_POST['event_description']);

    if (!empty($title) && !empty($date)) {
        $query = "UPDATE events SET title = ?, event_date = ?, description = ? WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "sssii", $title, $date, $description, $event_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Fetch portfolio items (unchanged)
$portfolio_query = "SELECT work_path, uploaded_at FROM portfolio WHERE user_id = ?";
$stmt = mysqli_prepare($db, $portfolio_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$portfolio_result = mysqli_stmt_get_result($stmt);
$portfolio_items = [];
while ($row = mysqli_fetch_assoc($portfolio_result)) {
    $portfolio_items[] = $row;
}
mysqli_stmt_close($stmt);

// Fetch upcoming events (visible to all users)
$events_query = "SELECT id, title, event_date, description FROM events WHERE user_id = ? AND event_date >= CURDATE() ORDER BY event_date ASC";
$stmt = mysqli_prepare($db, $events_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$events_result = mysqli_stmt_get_result($stmt);
$events = [];
while ($row = mysqli_fetch_assoc($events_result)) {
    $events[] = $row;
}
mysqli_stmt_close($stmt);

$cv_path = 'uploads\cv\CV.png';
$profile_image = 'uploads\cv\profile.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kimathi Joram - GIS Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; font-family: 'Roboto', sans-serif; background-color: #f8f9fa; color: #212529; line-height: 1.8; overflow-x: hidden;">
    <div class="container" style="max-width: 1280px; margin: 0 auto; padding: 0 20px;">
        <!-- Header -->
        <header style="background: linear-gradient(135deg, #1a3c34 0%, #2e7d32 100%); padding: 20px 0; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <div class="header-content" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div class="brand-container" style="display: flex; align-items: center;">
                    <span class="brand-mobile" style="color: #fff; font-size: 1.6em; font-weight: 700; letter-spacing: 1px;">Kimathi Joram</span>
                    <button class="hamburger" onclick="toggleMenu()" style="background: none; border: none; color: #fff; font-size: 1.8em; cursor: pointer; margin-left: 15px; transition: transform 0.3s ease;"><i class="fas fa-bars"></i></button>
                </div>
                <nav class="nav-menu" style="display: flex; gap: 20px; transition: transform 0.3s ease;">
                    <a href="#home" style="color: #fff; text-decoration: none; padding: 12px 15px; font-weight: 700; transition: all 0.3s ease;">Home</a>
                    <a href="#profile" style="color: #fff; text-decoration: none; padding: 12px 15px; transition: all 0.3s ease;">Profile</a>
                    <a href="#skills" style="color: #fff; text-decoration: none; padding: 12px 15px; transition: all 0.3s ease;">Skills</a>
                    <a href="#experience" style="color: #fff; text-decoration: none; padding: 12px 15px; transition: all 0.3s ease;">Experience</a>
                    <a href="#education" style="color: #fff; text-decoration: none; padding: 12px 15px; transition: all 0.3s ease;">Education</a>
                    <a href="#portfolio" style="color: #fff; text-decoration: none; padding: 12px 15px; transition: all 0.3s ease;">Portfolio</a>
                    <a href="#events" style="color: #fff; text-decoration: none; padding: 12px 15px; transition: all 0.3s ease;">Events</a>
                    <a href="#blog" style="color: #fff; text-decoration: none; padding: 12px 15px; transition: all 0.3s ease;">Blog</a>
                    <a href="#partner" style="color: #fff; text-decoration: none; padding: 12px 15px; transition: all 0.3s ease;">Partner</a>
                    <a href="logout.php" style="color: #fff; text-decoration: none; padding: 12px 15px; transition: all 0.3s ease;">Logout</a>
                </nav>
            </div>
        </header>

        <!-- Home Section -->
        <section id="home" style="padding: 80px 0; background: linear-gradient(135deg, #fff 60%, #e8f5e9 100%); text-align: center;">
            <div class="profile-container" style="max-width: 600px; margin: 0 auto;">
                <?php if (file_exists($profile_image)): ?>
                    <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Kimathi Joram" style="width: 200px; height: 200px; object-fit: cover; border-radius: 50%; margin-bottom: 25px; border: 4px solid #2e7d32; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                <?php else: ?>
                    <div style="width: 200px; height: 200px; border-radius: 50%; background-color: #ddd; margin: 0 auto 25px; display: flex; align-items: center; justify-content: center; color: #666;">Profile Image Not Found</div>
                <?php endif; ?>
                <h1 style="color: #1a3c34; font-size: 2.8em; font-weight: 700; margin-bottom: 15px;">Kimathi Joram</h1>
                <p style="color: #666; font-size: 1.3em; max-width: 800px; margin: 0 auto 20px; font-style: italic;">GIS Expert & Digital Content Creator</p>
                <p style="color: #666; font-size: 1.1em; max-width: 800px; margin: 0 auto;">A passionate GIS professional leveraging skills in spatial analysis, digital mapping, and content creation to solve real-world challenges.</p>
            </div>
        </section>

        <!-- Profile Section -->
        <section id="profile" style="padding: 60px 0; background: #f8f9fa;">
            <h2 style="color: #1a3c34; font-size: 2em; font-weight: 700; margin-bottom: 20px; text-align: center;">Profile</h2>
            <p style="color: #666; font-size: 1.1em; max-width: 800px; margin: 0 auto; text-align: center;">I am Kimathi Joram, a second-year B.Sc. student in Geographic Information Systems and Remote Sensing at Dedan Kimathi University of Technology. My career goal is to become a leading GIS professional, specializing in spatial analysis and digital mapping solutions to address real-world challenges.</p>
        </section>

        <!-- Skills Section -->
        <section id="skills" style="padding: 60px 0; background: #fff;">
            <h2 style="color: #1a3c34; font-size: 2em; font-weight: 700; margin-bottom: 30px; text-align: center;">Skills</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; max-width: 1000px; margin: 0 auto;">
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <h3 style="color: #2e7d32; font-size: 1.5em; margin-bottom: 15px;">Professional Skills</h3>
                    <p style="color: #666;"><strong>GIS Software:</strong> AutoCAD, ArcGIS, ArcMap, QGIS, ERDAS Imagine, GEE, PostgreSQL</p>
                    <p style="color: #666;"><strong>Programming:</strong> HTML, CSS, JS, Jupyter Notebook</p>
                </div>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <h3 style="color: #2e7d32; font-size: 1.5em; margin-bottom: 15px;">Personal Skills</h3>
                    <p style="color: #666;"><strong>Project Management:</strong> Google Meetings</p>
                    <p style="color: #666;"><strong>Web Development:</strong> Simple website creation, portal development</p>
                    <p style="color: #666;"><strong>Other Software:</strong> Google Earth Pro, Google Colab, Excel, Word, PowerPoint, QField, QField Cloud, NextCloud, JOSM</p>
                </div>
            </div>
        </section>

        <!-- Work Experience Section -->
        <section id="experience" style="padding: 60px 0; background: #f8f9fa;">
            <h2 style="color: #1a3c34; font-size: 2em; font-weight: 700; margin-bottom: 30px; text-align: center;">Work Experience</h2>
            <div style="max-width: 900px; margin: 0 auto;">
                <div style="margin-bottom: 30px; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <h3 style="color: #2e7d32; font-size: 1.5em; margin-bottom: 10px;">Content Creator | YouTube (Nov 2023 – Present)</h3>
                    <ul style="color: #666; padding-left: 20px; margin: 0;">
                        <li>Created and published tutorials on GIS software</li>
                        <li>Grew channel to 4000+ learners with high engagement</li>
                        <li>Managed production schedules and collaborated with followers</li>
                    </ul>
                </div>
                <div style="margin-bottom: 30px; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <h3 style="color: #2e7d32; font-size: 1.5em; margin-bottom: 10px;">Project Leader | GeoAlgorithm Aces (Aug – Oct 2024)</h3>
                    <ul style="color: #666; padding-left: 20px; margin: 0;">
                        <li>Developed GEEMAP-based tool for land use/cover assessment</li>
                        <li>Utilized Google Earth Engine and Google Colab</li>
                        <li>Achieved first place in competition</li>
                    </ul>
                </div>
                <div style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <h3 style="color: #2e7d32; font-size: 1.5em; margin-bottom: 10px;">Mapathon Participant (Oct 2024)</h3>
                    <ul style="color: #666; padding-left: 20px; margin: 0;">
                        <li>Contributed precise maps to HOT Tasking Manager</li>
                        <li>Ensured high-quality outputs for humanitarian tasks</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Education Section -->
        <section id="education" style="padding: 60px 0; background: #fff;">
            <h2 style="color: #1a3c34; font-size: 2em; font-weight: 700; margin-bottom: 30px; text-align: center;">Education</h2>
            <div style="max-width: 800px; margin: 0 auto; background: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <p style="color: #666; margin: 10px 0;"><strong>B.Sc. in Geographic Information Systems and Remote Sensing</strong><br>Dedan Kimathi University of Technology (Currently in Second Year, Semester 2)</p>
                <p style="color: #666; margin: 10px 0;"><strong>Python: Introduction to Data Science and Machine Learning A-Z</strong><br>Udemy, Certified on Sep 19, 2024</p>
            </div>
        </section>

        <!-- Portfolio Section -->
        <section id="portfolio" style="padding: 60px 0; background: #f8f9fa;">
            <div style="background-color: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); max-width: 900px; margin: 0 auto;">
                <h2 style="color: #1a3c34; font-size: 2em; font-weight: 700; margin-bottom: 20px; text-align: center;">Portfolio</h2>
                <?php if (file_exists($cv_path)): ?>
                    <div style="max-width: 100%; overflow-x: hidden;">
                        <img src="<?php echo htmlspecialchars($cv_path); ?>" alt="CV" style="width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-bottom: 15px; object-fit: contain; max-height: 600px;">
                    </div>
                    <p style="text-align: center;"><a href="<?php echo htmlspecialchars($cv_path); ?>" target="_blank" style="color: #2e7d32; text-decoration: none; font-weight: 500; transition: color 0.3s;">View Full CV <i class="fas fa-external-link-alt" style="margin-left: 5px;"></i></a></p>
                <?php else: ?>
                    <p style="color: #d32f2f; text-align: center;">CV not found.</p>
                <?php endif; ?>

                <h3 style="color: #1a3c34; font-size: 1.6em; font-weight: 700; margin: 30px 0 15px; text-align: center;">Upload Work Sample</h3>
                <form method="POST" enctype="multipart/form-data" style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-bottom: 30px;">
                    <input type="file" name="work" accept=".pdf,image/*" required style="flex: 1; min-width: 200px; padding: 12px; border: 2px dashed #ccc; border-radius: 8px; background-color: #f8f9fa;">
                    <button type="submit" name="upload_work" style="background-color: #2e7d32; color: #fff; padding: 12px 25px; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; transition: background-color 0.3s ease, transform 0.2s ease;">Upload</button>
                </form>

                <h3 style="color: #1a3c34; font-size: 1.6em; font-weight: 700; margin: 30px 0 15px; text-align: center;">Uploaded Works</h3>
                <?php if (empty($portfolio_items)): ?>
                    <p style="color: #666; text-align: center;">No work samples uploaded yet.</p>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <?php foreach ($portfolio_items as $item): ?>
                            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: transform 0.3s ease;">
                                <p style="margin: 0;"><a href="<?php echo htmlspecialchars($item['work_path']); ?>" target="_blank" style="color: #2e7d32; text-decoration: none; font-weight: 500;"><?php echo htmlspecialchars(pathinfo($item['work_path'], PATHINFO_BASENAME)); ?></a><br>
                                <span style="color: #999; font-size: 0.9em;">Uploaded: <?php echo htmlspecialchars($item['uploaded_at']); ?></span></p>
                                <?php if (in_array(mime_content_type($item['work_path']), ['image/jpeg', 'image/png', 'image/gif'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['work_path']); ?>" alt="Work Sample" style="width: 100%; height: auto; border-radius: 8px; margin-top: 10px; object-fit: cover; max-height: 200px;">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Events Section -->
        <section id="events" style="padding: 60px 0; background: #fff;">
            <h2 style="color: #1a3c34; font-size: 2em; font-weight: 700; margin-bottom: 20px; text-align: center;">Upcoming Events</h2>
            <div style="max-width: 900px; margin: 0 auto;">
                <!-- Add Event Form (visible only to celestakim018@gmail.com) -->
                <?php if ($user_email === 'celestakim018@gmail.com'): ?>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 30px;">
                        <h3 style="color: #2e7d32; font-size: 1.5em; margin-bottom: 15px; text-align: center;">Add New Event</h3>
                        <form method="POST" action="portfolio.php" style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;">
                            <input type="text" name="event_title" placeholder="Event Title" required style="flex: 1; min-width: 200px; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;">
                            <input type="date" name="event_date" required style="flex: 1; min-width: 200px; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;">
                            <textarea name="event_description" placeholder="Event Description" rows="3" style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;"></textarea>
                            <button type="submit" name="add_event" style="background-color: #2e7d32; color: #fff; padding: 12px 25px; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; transition: background-color 0.3s ease, transform 0.2s ease;">Add Event</button>
                        </form>
                    </div>

                    <!-- Event List with Update Forms (only for celestakim018@gmail.com) -->
                    <div class="event-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                        <?php foreach ($events as $event): ?>
                            <div class="event-card" style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 8px 16px rgba(46, 125, 50, 0.2); animation: slideIn 0.5s ease-out forwards;">
                                <h4 style="color: #1a3c34; font-size: 1.3em; margin-bottom: 10px;"><?php echo htmlspecialchars($event['title']); ?></h4>
                                <p style="color: #666; font-size: 1em; margin-bottom: 10px;"><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                <p style="color: #666; font-size: 1em; margin-bottom: 15px;"><?php echo htmlspecialchars($event['description'] ?? 'No description provided'); ?></p>
                                <form method="POST" action="portfolio.php" style="display: flex; flex-direction: column; gap: 10px;">
                                    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                                    <input type="text" name="event_title" value="<?php echo htmlspecialchars($event['title']); ?>" required style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                                    <input type="date" name="event_date" value="<?php echo htmlspecialchars($event['event_date']); ?>" required style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                                    <textarea name="event_description" rows="2" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;"><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
                                    <button type="submit" name="update_event" style="background-color: #1a3c34; color: #fff; padding: 8px 15px; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; transition: background-color 0.3s ease;">Update</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Event List (visible to all users, no update forms) -->
                    <?php if (empty($events)): ?>
                        <p style="color: #666; text-align: center;">No upcoming events scheduled.</p>
                    <?php else: ?>
                        <div class="event-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                            <?php foreach ($events as $event): ?>
                                <div class="event-card" style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 8px 16px rgba(46, 125, 50, 0.2); animation: slideIn 0.5s ease-out forwards;">
                                    <h4 style="color: #1a3c34; font-size: 1.3em; margin-bottom: 10px;"><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <p style="color: #666; font-size: 1em; margin-bottom: 10px;"><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                    <p style="color: #666; font-size: 1em;"><?php echo htmlspecialchars($event['description'] ?? 'No description provided'); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Blog Section -->
        <section id="blog" style="padding: 60px 0; background: #fff;">
            <h2 style="color: #1a3c34; font-size: 2em; font-weight: 700; margin-bottom: 20px; text-align: center;">Blog</h2>
            <p style="color: #666; text-align: center; max-width: 800px; margin: 0 auto;">Coming soon! Check back for GIS tutorials and insights.</p>
        </section>

        <!-- Partner with Me Section -->
        <section id="partner" style="padding: 60px 0; background: #f8f9fa;">
            <h2 style="color: #1a3c34; font-size: 2em; font-weight: 700; margin-bottom: 20px; text-align: center;">Partner with Me</h2>
            <div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <p style="color: #666; margin: 10px 0;"><strong>Email:</strong> celestakim018@gmail.com</p>
                <p style="color: #666; margin: 10px 0;"><strong>Phone:</strong> +2547 0278 1490</p>
                <p style="color: #666; margin: 10px 0;"><strong>Location:</strong> Kimathi, Nyeri, Kenya</p>
                <?php if (isset($partnership_error)): ?>
                    <p style="color: #d32f2f; text-align: center; margin: 10px 0;"><?php echo htmlspecialchars($partnership_error); ?></p>
                <?php elseif (isset($partnership_success)): ?>
                    <p style="color: #2e7d32; text-align: center; margin: 10px 0;"><?php echo htmlspecialchars($partnership_success); ?></p>
                <?php endif; ?>
                <form method="POST" action="portfolio.php" style="margin-top: 20px;">
                    <input type="text" name="company_name" placeholder="Company Name" required style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;">
                    <input type="email" name="contact_email" placeholder="Your Email" required style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;">
                    <textarea name="message" placeholder="Your Message" rows="5" required style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;"></textarea>
                    <button type="submit" name="submit_partnership" style="background-color: #2e7d32; color: #fff; padding: 12px 25px; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; transition: background-color 0.3s ease, transform 0.2s ease; width: 100%;">Send Partnership Request</button>
                </form>
            </div>
        </section>

        <!-- Footer -->
        <footer style="background: linear-gradient(135deg, #1a3c34 0%, #2e7d32 100%); color: #fff; padding: 40px 0; text-align: center;">
            <p style="margin: 10px 0;">© 2025 Kimathi Joram - GIS Expert</p>
            <p style="margin: 10px 0;">References: <a href="mailto:gdevdekut@gmail.com" style="color: #fff; text-decoration: none; transition: color 0.3s;">DeKUT G-Dev Club</a> | <a href="https://iggres.dkut.ac.ke/" style="color: #fff; text-decoration: none; transition: color 0.3s;">DeKUT IGGRES</a></p>
        </footer>

        <!-- Back to Index Button -->
        <a href="index.php" style="position: fixed; bottom: 20px; left: 20px; background-color: #2e7d32; color: #fff; padding: 10px 15px; border-radius: 50%; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: background-color 0.3s ease, transform 0.2s ease; z-index: 1000;">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>

    <script>
        function toggleMenu() {
            const nav = document.querySelector('.nav-menu');
            const mainContent = document.querySelector('main');
            const hamburger = document.querySelector('.hamburger i');
            nav.classList.toggle('active');
            if (window.innerWidth <= 768) {
                if (nav.classList.contains('active')) {
                    nav.style.transform = 'translateX(0)';
                    mainContent.style.marginLeft = '250px';
                } else {
                    nav.style.transform = 'translateX(-100%)';
                    mainContent.style.marginLeft = '0';
                }
            }
            hamburger.classList.toggle('fa-bars');
            hamburger.classList.toggle('fa-times');
        }

        document.querySelector('.nav-menu').addEventListener('mouseleave', function() {
            if (window.innerWidth <= 768 && this.classList.contains('active')) {
                this.classList.remove('active');
                this.style.transform = 'translateX(-100%)';
                document.querySelector('main').style.marginLeft = '0';
                const hamburger = document.querySelector('.hamburger i');
                hamburger.classList.add('fa-bars');
                hamburger.classList.remove('fa-times');
            }
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        const buttons = document.querySelectorAll('button');
        buttons.forEach(btn => {
            btn.addEventListener('mouseover', () => btn.style.transform = 'translateY(-2px)');
            btn.addEventListener('mouseout', () => btn.style.transform = 'translateY(0)');
        });
    </script>

    <style>
        .hamburger { display: none; }
        .brand-mobile { display: none; }

        /* Animation for event cards */
        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .event-card {
            animation: slideIn 0.5s ease-out forwards;
        }

        @media (max-width: 768px) {
            .hamburger { 
                display: block; 
                visibility: visible; 
            }
            .brand-mobile { 
                display: block; 
            }
            .nav-menu {
                position: fixed;
                top: 0;
                left: 0;
                width: 250px;
                height: 100%;
                background: rgba(26, 60, 52, 0.95);
                flex-direction: column;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                padding-top: 80px;
                box-shadow: 4px 0 12px rgba(0,0,0,0.2);
                z-index: 999;
            }
            .nav-menu.active {
                transform: translateX(0);
            }
            .nav-menu a {
                display: block;
                padding: 15px 20px;
                border-bottom: none;
                transition: background-color 0.3s;
            }
            .nav-menu a:hover {
                background-color: rgba(255, 255, 255, 0.1);
            }
            main {
                margin-left: 0;
                transition: margin-left 0.3s ease;
            }
            .profile-container img {
                width: 150px;
                height: 150px;
            }
            iframe, .portfolio-section img {
                height: 400px;
            }
        }

        @media (min-width: 769px) {
            .nav-menu { 
                transform: none !important; 
                position: static;
                background: none;
                padding: 0;
                box-shadow: none;
                width: auto;
                height: auto;
            }
            .nav-menu a:hover { 
                color: #b2dfdb; 
                border-bottom: 2px solid #fff; 
            }
            button:hover { 
                background-color: #1b5e20; 
            }
            a:hover { 
                color: #66bb6a; 
            }
            footer a:hover { 
                color: #b2dfdb; 
            }
            main { 
                margin-left: 0; 
            }
        }
    </style>
</body>
</html>