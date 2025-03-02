<?php
require 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if (isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : NULL;
    $is_student = $_POST['is_student'];
    $university = $is_student === 'yes' ? trim($_POST['university']) : NULL;
    $non_student_role = $is_student === 'no' ? trim($_POST['non_student_role']) : NULL;
    $agree_terms = isset($_POST['agree_terms']) ? 1 : 0;
    $referral_source = $_POST['referral_source'];
    $referral_details = !empty($_POST['referral_details']) ? trim($_POST['referral_details']) : NULL;
    $country = 'Kenya';

    if (empty($username) || empty($email) || empty($password) || !$agree_terms || ($is_student === 'yes' && empty($university)) || ($is_student === 'no' && empty($non_student_role))) {
        $error = "All required fields must be filled, and terms must be agreed.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($db, $check_query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $error = "Username or email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (username, email, password, phone, country, is_student, university, non_student_role, agree_terms, referral_source, referral_details) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $insert_query);
            mysqli_stmt_bind_param($stmt, "ssssssssiss", $username, $email, $hashed_password, $phone, $country, $is_student, $university, $non_student_role, $agree_terms, $referral_source, $referral_details);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['user_id'] = mysqli_insert_id($db);
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit;
            } else {
                $error = "Signup failed: " . mysqli_error($db);
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GIS Insights - Signup</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <span class="brand-mobile">CELESTA KIM GIS Insights</span>
                <button class="hamburger" onclick="toggleMenu()">☰</button>
                <nav class="nav-menu">
                    <a href="index.php" style="font-weight: bold;">GIS Insights</a>
                    <div class="dropdown">
                        <a href="explore.php">Explore</a>
                        <div class="dropdown-content">
                            <a href="explore.php#population">Population Density</a>
                            <a href="explore.php#traffic">Traffic Patterns</a>
                            <a href="explore.php#deforestation">Deforestation Tracking</a>
                            <a href="explore.php#flood">Flood Risk</a>
                            <a href="explore.php#wildlife">Wildlife Habitats</a>
                        </div>
                    </div>
                    <a href="about.php">About</a>
                    <a href="portfolio.php">Portfolio</a>
                    <a href="insight.php">Insights</a>
                    <a href="blog.php">Blog</a>
                    <a href="login.php">Login</a>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <div class="form-container">
                <h2>Sign Up</h2>
                <?php if (!empty($error)): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <form method="POST" action="signup.php">
                    <input type="text" name="username" placeholder="Username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required><br>
                    <input type="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required><br>
                    <input type="password" name="password" placeholder="Password" required><br>
                    <input type="tel" name="phone" placeholder="Phone (optional)" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"><br>
                    <label>Country: Kenya</label><input type="hidden" name="country" value="Kenya"><br>
                    <label>Are you a student?</label>
                    <select name="is_student" id="is_student" onchange="toggleStudentFields()" required>
                        <option value="">Select</option>
                        <option value="yes" <?php echo isset($_POST['is_student']) && $_POST['is_student'] === 'yes' ? 'selected' : ''; ?>>Yes</option>
                        <option value="no" <?php echo isset($_POST['is_student']) && $_POST['is_student'] === 'no' ? 'selected' : ''; ?>>No</option>
                    </select><br>
                    <div id="student_fields" class="hidden">
                        <label>University:</label>
                        <select name="university" id="university">
                            <option value="">Select University</option>
                            <?php
                            $universities = ["University of Nairobi", "Kenyatta University", "Moi University", "Egerton University", "Jomo Kenyatta University of Agriculture and Technology", "Maseno University", "Technical University of Kenya", "Dedan Kimathi University of Technology"];
                            foreach ($universities as $uni) {
                                $selected = (isset($_POST['university']) && $_POST['university'] === $uni) ? 'selected' : '';
                                echo "<option value='$uni' $selected>$uni</option>";
                            }
                            ?>
                        </select><br>
                    </div>
                    <div id="non_student_fields" class="hidden">
                        <input type="text" name="non_student_role" placeholder="Your Role" value="<?php echo isset($_POST['non_student_role']) ? htmlspecialchars($_POST['non_student_role']) : ''; ?>"><br>
                    </div>
                    <label><input type="checkbox" name="agree_terms" <?php echo isset($_POST['agree_terms']) ? 'checked' : ''; ?> required> I agree to the <a href="terms.php">terms</a></label><br>
                    <label>How did you find this website?</label>
                    <select name="referral_source" id="referral_source" onchange="toggleReferralDetails()" required>
                        <option value="">Select</option>
                        <option value="socials" <?php echo isset($_POST['referral_source']) && $_POST['referral_source'] === 'socials' ? 'selected' : ''; ?>>Social Media</option>
                        <option value="school" <?php echo isset($_POST['referral_source']) && $_POST['referral_source'] === 'school' ? 'selected' : ''; ?>>School</option>
                        <option value="friends" <?php echo isset($_POST['referral_source']) && $_POST['referral_source'] === 'friends' ? 'selected' : ''; ?>>Friends</option>
                        <option value="other" <?php echo isset($_POST['referral_source']) && $_POST['referral_source'] === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select><br>
                    <div id="referral_details" class="hidden">
                        <input type="text" name="referral_details" placeholder="Specify" value="<?php echo isset($_POST['referral_details']) ? htmlspecialchars($_POST['referral_details']) : ''; ?>"><br>
                    </div>
                    <button type="submit" name="signup">Sign Up</button>
                    <p>Already have an account? <a href="login.php">Sign In</a></p>
                </form>
            </div>
        </main>

        <footer>
            <div class="footer-content">
                <nav>
                    <p style="font-family: 'Inconsolata', monospace;">GIS Insights</p>
                    <a href="about.php">About</a>
                    <a href="terms.php">Terms</a>
                    <a href="blog.php">Blog</a>
                </nav>
                <div>
                    <p style="font-family: 'Inconsolata', monospace;">© 2025 GIS Insights</p>
                    <p style="font-family: 'Inconsolata', monospace;">Created by: Kimathi Joram - GIS Enthusiast at Dedan Kimathi University</p>
                    <div class="social-links">
                        <a href="https://x.com/celestakim018" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.youtube.com/@CELESTAKIM_GIS" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="https://www.linkedin.com/in/celesta-kim-21020232b/" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        <a href="https://github.com/CELESTAKIM" target="_blank" aria-label="GitHub"><i class="fab fa-github"></i></a>
                        <a href="https://www.facebook.com/Eng.CelestaKim/" target="_blank" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/celestakim_gis/" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.pinterest.com/celestakim018/" target="_blank" aria-label="Pinterest"><i class="fab fa-pinterest"></i></a>
                        <a href="https://mastodon.social/@CELESTAKIM_GIS" target="_blank" aria-label="Mastodon"><i class="fas fa-globe"></i></a>
                    </div>
                    <div class="partnerships">
                        <p>Partnerships: 
                            <!-- <a href="https://ksaspaceborne.ai">KSA Spaceborne AI</a> | 
                            <a href="https://tasks.hotosm.org">HOT Task OSM</a> |  -->
                            <a href="https://gdevdekut.org">GDeVDeKUT</a> | 
                            <!-- <a href="https://udemy.com">Udemy</a> -->
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        function toggleStudentFields() {
            const isStudent = document.getElementById('is_student').value;
            document.getElementById('student_fields').classList.toggle('hidden', isStudent !== 'yes');
            document.getElementById('non_student_fields').classList.toggle('hidden', isStudent !== 'no');
            document.getElementById('university').required = isStudent === 'yes';
            document.getElementById('non_student_role').required = isStudent === 'no';
        }

        function toggleReferralDetails() {
            const source = document.getElementById('referral_source').value;
            document.getElementById('referral_details').classList.toggle('hidden', source !== 'other');
            document.getElementById('referral_details').querySelector('input').required = source === 'other';
        }

        toggleStudentFields();
        toggleReferralDetails();

        function toggleMenu() {
            const nav = document.querySelector('.nav-menu');
            nav.classList.toggle('active');
        }

        document.querySelector('.nav-menu').addEventListener('mouseleave', function() {
            if (window.innerWidth <= 768) this.classList.remove('active');
        });
    </script>
</body>
</html>