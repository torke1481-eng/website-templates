# PROMPT CLAUDE - PARTE 2: TEMPLATE HTML CON CSS INLINE

## TEMPLATE COMPLETO (COPIAR TODO)

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[nombre_negocio] | [info_negocio.ciudad]</title>
    <meta name="description" content="[seo.meta_description O GENERAR: descripción de 150 chars del negocio]">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>[diseno.emoji_logo]</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: [colores_principales[0] O #007bff];
            --secondary: [colores_principales[1] O #0056b3];
            --accent: [colores_principales[2] O #1a1a2e];
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: #1a1a1a; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        
        /* Buttons */
        .btn { padding: 16px 32px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; cursor: pointer; border: none; font-size: 16px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--secondary); transform: translateY(-2px); }
        .btn-secondary { background: transparent; color: var(--primary); border: 2px solid var(--primary); }
        .btn-secondary:hover { background: var(--primary); color: white; }
        .btn-white { background: white; color: var(--primary); }
        
        /* Header */
        .header { position: fixed; top: 0; left: 0; right: 0; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); z-index: 1000; padding: 16px 0; box-shadow: 0 2px 20px rgba(0,0,0,0.1); }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; }
        .logo-icon { font-size: 32px; }
        .logo-text { font-weight: 700; font-size: 20px; }
        .nav { display: flex; gap: 32px; }
        .nav a { text-decoration: none; color: #1a1a1a; font-weight: 500; }
        .nav a:hover { color: var(--primary); }
        
        /* Hero */
        .hero { min-height: 100vh; display: flex; align-items: center; padding: 120px 0 80px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); position: relative; }
        .hero-content { max-width: 700px; color: white; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 50px; font-size: 14px; margin-bottom: 24px; }
        .hero h1 { font-size: clamp(36px, 5vw, 56px); font-weight: 900; line-height: 1.1; margin-bottom: 24px; }
        .hero-subtitle { font-size: 20px; opacity: 0.9; margin-bottom: 32px; }
        .hero-buttons { display: flex; gap: 16px; flex-wrap: wrap; }
        
        /* Stats */
        .stats { padding: 60px 0; background: #f8f9fa; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 32px; text-align: center; }
        .stat h3 { font-size: 42px; font-weight: 800; color: var(--primary); }
        .stat p { color: #666; font-weight: 500; }
        
        /* Sections */
        .section { padding: 80px 0; }
        .section-header { text-align: center; margin-bottom: 50px; }
        .section-badge { display: inline-block; background: rgba(0,123,255,0.1); color: var(--primary); padding: 8px 16px; border-radius: 50px; font-size: 14px; font-weight: 600; margin-bottom: 16px; }
        .section-title { font-size: 36px; font-weight: 800; margin-bottom: 16px; }
        .section-subtitle { font-size: 18px; color: #666; max-width: 600px; margin: 0 auto; }
        
        /* Features */
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
        .feature-card { background: white; padding: 32px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.3s; }
        .feature-card:hover { transform: translateY(-8px); }
        .feature-icon { font-size: 40px; margin-bottom: 16px; }
        .feature-card h3 { font-size: 20px; font-weight: 700; margin-bottom: 12px; }
        .feature-card p { color: #666; }
        
        /* Process */
        .process-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; }
        .process-step { text-align: center; padding: 24px; }
        .process-num { width: 50px; height: 50px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; margin: 0 auto 16px; }
        .process-step h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
        .process-step p { color: #666; font-size: 14px; }
        
        /* About */
        .about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .about-content h2 { font-size: 32px; font-weight: 800; margin-bottom: 20px; }
        .about-content > p { color: #666; margin-bottom: 24px; }
        .about-list { list-style: none; margin-bottom: 24px; }
        .about-list li { padding: 10px 0; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 10px; }
        .about-list li::before { content: '✓'; color: var(--primary); font-weight: 700; }
        .about-img { border-radius: 16px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
        
        /* Testimonials */
        .testimonials-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
        .testimonial { background: white; padding: 28px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .testimonial-stars { color: #ffc107; margin-bottom: 12px; }
        .testimonial-text { font-style: italic; margin-bottom: 16px; color: #333; }
        .testimonial-author { display: flex; align-items: center; gap: 12px; }
        .testimonial-avatar { width: 45px; height: 45px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; }
        .testimonial-name { font-weight: 600; }
        .testimonial-role { font-size: 13px; color: #666; }
        
        /* FAQ */
        .faq-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 50px; }
        .faq-header h2 { font-size: 32px; font-weight: 800; margin-bottom: 16px; }
        .faq-header p { color: #666; margin-bottom: 20px; }
        .faq-item { background: white; border-radius: 12px; margin-bottom: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden; }
        .faq-q { padding: 18px 20px; font-weight: 600; cursor: pointer; display: flex; justify-content: space-between; }
        .faq-q::after { content: '+'; color: var(--primary); font-size: 20px; }
        .faq-a { padding: 0 20px 18px; color: #666; }
        
        /* CTA */
        .cta { padding: 80px 0; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); text-align: center; color: white; }
        .cta h2 { font-size: 36px; font-weight: 800; margin-bottom: 16px; }
        .cta p { font-size: 18px; opacity: 0.9; margin-bottom: 28px; max-width: 500px; margin-left: auto; margin-right: auto; }
        .cta-buttons { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
        
        /* Contact */
        .contact { padding: 80px 0; background: #f8f9fa; }
        .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; }
        .contact-form { background: white; padding: 36px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px 14px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); }
        .contact-info h3 { font-size: 24px; font-weight: 700; margin-bottom: 20px; }
        .contact-item { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
        .contact-icon { width: 45px; height: 45px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        
        /* WhatsApp Float */
        .wa-float { position: fixed; bottom: 24px; right: 24px; width: 60px; height: 60px; background: #25d366; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 4px 20px rgba(37,211,102,0.4); z-index: 999; text-decoration: none; }
        .wa-float:hover { transform: scale(1.1); }
        
        /* Footer */
        .footer { background: var(--accent); color: white; padding: 50px 0 24px; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 36px; margin-bottom: 36px; }
        .footer-brand p { opacity: 0.7; margin-top: 12px; }
        .footer h4 { font-size: 16px; font-weight: 700; margin-bottom: 16px; }
        .footer ul { list-style: none; }
        .footer ul li { margin-bottom: 10px; }
        .footer ul a { color: rgba(255,255,255,0.7); text-decoration: none; }
        .footer ul a:hover { color: white; }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 24px; display: flex; justify-content: space-between; align-items: center; font-size: 14px; opacity: 0.7; }
        .social-links { display: flex; gap: 12px; }
        .social-links a { width: 36px; height: 36px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav, .header-cta { display: none; }
            .hero h1 { font-size: 32px; }
            .about-grid, .contact-grid, .faq-grid, .footer-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
```

---

## CONTINÚA EN PARTE 3 (ESTRUCTURA HTML)
