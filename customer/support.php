<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Neural Help Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        
        :root {
            --neural-cyan: #2DD4BF;
            --neural-bg: #030712;
            --neural-card: #111827;
        }

        body { 
            background: var(--neural-bg); 
            color: white; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            min-height: 100vh;
            display: flex;
            align-items: center;
            background-image: radial-gradient(circle at 50% 50%, rgba(45, 212, 191, 0.05) 0%, transparent 70%);
        }

        .support-card {
            background: var(--neural-card);
            border: 1px solid rgba(45, 212, 191, 0.1);
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            backdrop-filter: blur(10px);
        }

        .form-label {
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.5);
            font-weight: 800;
        }

        .form-control, .form-select {
            background: rgba(255,255,255,0.03) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: white !important;
            border-radius: 16px;
            padding: 12px 15px;
            transition: 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--neural-cyan) !important;
            box-shadow: 0 0 0 4px rgba(45, 212, 191, 0.1);
        }

        .btn-send {
            background: var(--neural-cyan);
            color: #030712;
            font-weight: 800;
            border: none;
            border-radius: 16px;
            padding: 15px;
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-send:hover {
            background: #22d3ee;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(45, 212, 191, 0.3);
        }

        .contact-info-pill {
            background: rgba(45, 212, 191, 0.08);
            border: 1px solid rgba(45, 212, 191, 0.2);
            border-radius: 50px;
            padding: 10px 20px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--neural-cyan);
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 25px;
        }

        /* Styling the dropdown options for dark mode */
        option { background: #1f2937; color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="support-card animate__animated animate__fadeInUp">
                <div class="text-center mb-4">
                    <div class="contact-info-pill">
                        <i class="bi bi-shield-check"></i> Encrypted Medical Support
                    </div>
                    <h2 class="fw-800" style="letter-spacing: -1px;">Technical Help</h2>
                    <p class="text-muted small">Synchronizing you with our pharmacy team.</p>
                </div>

                <form id="contactForm">
                    <div class="mb-3">
                        <label class="form-label text-uppercase">Inquiry Type</label>
                        <select id="subject" name="subject" class="form-select" required>
                            <option value="Order Issue">Order Tracking / Issue</option>
                            <option value="Medical Inquiry">Medicine Dosage / Info</option>
                            <option value="Billing">Refunds & Payments</option>
                            <option value="Technical">Website Technical Bug</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-uppercase">Message Detail</label>
                        <textarea id="message" name="message" class="form-control" rows="4" placeholder="How can we assist you today?" required></textarea>
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-send w-100">
                        <span id="btnText">TRANSMIT MESSAGE</span>
                        <div id="btnLoader" class="spinner-border spinner-border-sm d-none" role="status"></div>
                    </button>
                </form>

                <div id="responseMsg" class="mt-4 text-center d-none"></div>

                <div class="mt-5 pt-4 border-top border-white border-opacity-10 text-center">
                    <a href="dashboard.php" class="text-decoration-none text-muted small fw-bold hover-cyan">
                        <i class="bi bi-cpu me-1"></i> Return to MIMS Terminal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnLoader = document.getElementById('btnLoader');
    const responseMsg = document.getElementById('responseMsg');
    
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value;

    btn.disabled = true;
    btnText.classList.add('d-none');
    btnLoader.classList.remove('d-none');

    // Passing both subject and message to contact.php
    fetch('contact.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `subject=${encodeURIComponent(subject)}&message=${encodeURIComponent(message)}`
    })
    .then(response => response.json())
    .then(data => {
        responseMsg.classList.remove('d-none');
        if(data.status === 'success') {
            responseMsg.innerHTML = `<span style="color:var(--neural-cyan);" class="fw-bold"><i class="bi bi-broadcast"></i> Transmission Successful!</span>`;
            document.getElementById('contactForm').reset();
        } else {
            responseMsg.innerHTML = `<span class="text-danger fw-bold">Link Error: ${data.message}</span>`;
        }
    })
    .catch(error => {
        responseMsg.innerHTML = `<span class="text-danger">Network Error. Check Terminal.</span>`;
    })
    .finally(() => {
        btn.disabled = false;
        btnText.classList.remove('d-none');
        btnLoader.classList.add('d-none');
    });
});
</script>

</body>
</html>