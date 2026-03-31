<?php
session_start();

// Clean expired CVs
if(isset($_SESSION['cv_data'])){
    foreach($_SESSION['cv_data'] as $id => $data){
        if(isset($data['expires']) && $data['expires'] < time()){
            unset($_SESSION['cv_data'][$id]);
        }
    }
}

// Check if CV data exists in session or via GET parameter
$cvId = $_GET['cv'] ?? $_SESSION['current_cv_id'] ?? null;

if (!$cvId || !isset($_SESSION['cv_data'][$cvId])) {
    // No valid CV found, redirect to form
    header('Location: index.html');
    exit;
}

// Check if CV expired
if($_SESSION['cv_data'][$cvId]['expires'] < time()){
    unset($_SESSION['cv_data'][$cvId]);
    header('Location: index.html');
    exit;
}

$cvData = $_SESSION['cv_data'][$cvId];
extract($cvData); // Extract all CV data into variables
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($name ?: 'CV'); ?>'s CV - shanczan</title>
    <link rel="stylesheet" href="stylish.css">
    <style>
        .header-section {
            max-width: 900px;
            margin: 0 auto 40px;
            padding: 0 20px;
        }
        .share-url {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f9f0 100%);
            border: 2px solid #4caf50;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        .share-url strong {
            color: #2e7d32;
            font-size: 18px;
        }
        .share-url code {
            background: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 14px;
            word-break: break-all;
            display: block;
            margin: 10px 0;
        }
        .copy-btn {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }
        .copy-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 12px 24px;
            background: #f8f9fa;
            color: #8b3fa6 !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="header-section">
        <div class="share-url">
            <strong>📋 Share this CV:</strong><br>
            <code id="shareUrl"><?php echo 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/output.php?cv=' . $cvId; ?></code>
            <br>
            <button class="copy-btn" onclick="copyUrl()">📋 Copy Link</button>
        </div>
        <a href="index.html" class="back-link">← Create New CV</a>
    </div>

    <div class="resume">
        <div class="sidebar">
            <div class="profile-img">
                <?php if(!empty($imagePath) && file_exists($imagePath)): ?>
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Profile Picture">
                <?php else: ?>
                    <div class="no-image">📷</div>
                <?php endif; ?>
            </div>

            <h2 class="name"><?php echo htmlspecialchars($name ?: 'Your Name'); ?></h2>
            <p class="role"><?php echo htmlspecialchars($program ?: 'Your Program'); ?></p>

            <div class="sidebar-section">
                <h3>📞 Contacts</h3>
                <p><?php echo htmlspecialchars($phone ?: 'Not provided'); ?></p>
                <p><?php echo htmlspecialchars($email ?: 'Not provided'); ?></p>
                <p><?php echo htmlspecialchars($address ?: 'Not provided'); ?></p>
            </div>

            <div class="sidebar-section">
                <h3>💻 Skills</h3>
                <ul>
                    <?php
                    $skillsList = array_filter(array_map('trim', explode("\n", $skills)));
                    if(empty($skillsList)){
                        echo "<li>No skills provided</li>";
                    } else {
                        foreach($skillsList as $skill){
                            echo "<li>" . htmlspecialchars($skill) . "</li>";
                        }
                    }
                    ?>
                </ul>
            </div>

            <div class="sidebar-section">
                <h3>🌍 Languages</h3>
                <ul>
                    <?php
                    $languagesList = array_filter(array_map('trim', explode("\n", $languages)));
                    if(empty($languagesList)){
                        echo "<li>No languages provided</li>";
                    } else {
                        foreach($languagesList as $language){
                            echo "<li>" . htmlspecialchars($language) . "</li>";
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>

        <div class="main">
            <div class="section">
                <h2>👤 Profile</h2>
                <p>
                    I am a motivated <?php echo htmlspecialchars(strtolower($gender ?: 'professional')); ?> aged <?php echo htmlspecialchars($age ?: 'XX'); ?> currently studying 
                    <strong><?php echo htmlspecialchars($program ?: 'XXX'); ?></strong>. I enjoy learning new technologies and improving my skills while 
                    working collaboratively with others.
                </p>
            </div>

            <div class="section">
                <h2>🎓 Education</h2>
                <?php if($education): ?>
                    <p><?php echo nl2br(htmlspecialchars($education)); ?></p>
                <?php else: ?>
                    <p>No education details provided.</p>
                <?php endif; ?>
            </div>

            <div class="section">
                <h2>ℹ️ Personal Information</h2>
                <p><strong>Age:</strong> <?php echo htmlspecialchars($age ?: 'Not provided'); ?></p>
                <p><strong>Gender:</strong> <?php echo htmlspecialchars($gender ?: 'Not provided'); ?></p>
               <?php if($facebook): ?>
                <p><strong>Facebook:</strong> <a href="<?php echo htmlspecialchars($facebook); ?>" target="_blank"><?php echo htmlspecialchars(basename(parse_url($facebook, PHP_URL_PATH))); ?></a></p>
               <?php endif; ?>
            </div>

            <div class="footer">
                <a href="index.html">
                    <button>New CV</button>
                </a>
                <button id="downloadBtn" onclick="downloadPDF()">📥 Download PDF</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function copyUrl() {
            const url = document.getElementById('shareUrl').textContent;
            navigator.clipboard.writeText(url).then(() => {
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = '✅ Copied!';
                btn.style.background = 'linear-gradient(135deg, #2e7d32 0%, #4caf50 100%)';
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = 'linear-gradient(135deg, #4caf50 0%, #45a049 100%)';
                }, 2000);
            }).catch(() => {
                // Fallback
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Link copied!');
            });
        }

        function downloadPDF(){
            const downloadBtn = document.getElementById("downloadBtn");
            const headerSection = document.querySelector('.header-section');
            const footer = document.querySelector(".footer");
            
            // Hide header and footer for clean PDF
            headerSection.style.display = "none";
            footer.style.display = "none";
            
            downloadBtn.textContent = "⏳ Generating...";
            downloadBtn.disabled = true;

            const element = document.querySelector(".resume");
            const opt = {
                margin: 0.5,
                filename: '<?php echo addslashes($name ?: "CV"); ?>_Resume.pdf',
                image: {type: 'jpeg', quality: 0.98},
                html2canvas: {scale: 2, useCORS: true},
                jsPDF: {unit: 'in', format: 'a4', orientation: 'portrait'}
            };

            html2pdf().set(opt).from(element).save().then(()=>{
                headerSection.style.display = "block";
                footer.style.display = "flex";
                downloadBtn.textContent = "📥 Download PDF";
                downloadBtn.disabled = false;
            }).catch((error) => {
                headerSection.style.display = "block";
                footer.style.display = "flex";
                downloadBtn.textContent = "📥 Download PDF";
                downloadBtn.disabled = false;
                console.error('PDF Error:', error);
                alert("PDF generation failed. Try Ctrl+P instead.");
            });
        }
    </script>
</body>
</html>