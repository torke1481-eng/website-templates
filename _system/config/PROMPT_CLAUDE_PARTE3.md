# PROMPT CLAUDE - PARTE 3: ESTRUCTURA HTML (BODY)

## CONTINÚA EL HTML (DESPUÉS DEL </head><body>)

```html
    <!-- HEADER -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="#" class="logo">
                    <span class="logo-icon">[diseno.emoji_logo]</span>
                    <span class="logo-text">[nombre_negocio]</span>
                </a>
                <nav class="nav">
                    <a href="#inicio">Inicio</a>
                    <a href="#servicios">Servicios</a>
                    <a href="#nosotros">Nosotros</a>
                    <a href="#contacto">Contacto</a>
                </nav>
                <a href="https://wa.me/[WHATSAPP_SOLO_NUMEROS]" class="btn btn-primary" style="padding:12px 20px;">💬 WhatsApp</a>
            </div>
        </div>
    </header>

    <!-- HERO -->
    <section id="inicio" class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    [diseno.emoji_logo] [contenido.badge_hero O "⭐ Profesionales de Confianza"]
                </div>
                <h1>[contenido.titulo_hero]</h1>
                <p class="hero-subtitle">[contenido.subtitulo_hero]</p>
                <div class="hero-buttons">
                    <a href="https://wa.me/[WHATSAPP_SOLO_NUMEROS]?text=Hola%20vi%20su%20página%20y%20me%20interesa%20información" class="btn btn-white">[contenido.cta_principal O "Contáctanos"] →</a>
                    <a href="#servicios" class="btn btn-secondary" style="border-color:white;color:white;">[contenido.cta_secundario O "Ver Servicios"]</a>
                </div>
            </div>
        </div>
    </section>

    <!-- STATS (SOLO SI contenido.stats TIENE DATOS, SI NO OMITIR TODA LA SECCIÓN) -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <!-- REPETIR PARA CADA stat EN contenido.stats: -->
                <div class="stat">
                    <h3>[stat.numero]</h3>
                    <p>[stat.label]</p>
                </div>
                <!-- FIN REPETIR -->
            </div>
        </div>
    </section>

    <!-- SERVICIOS -->
    <section id="servicios" class="section">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Nuestros Servicios</span>
                <h2 class="section-title">Lo Que Ofrecemos</h2>
                <p class="section-subtitle">Soluciones profesionales para ti</p>
            </div>
            <div class="features-grid">
                <!-- REPETIR PARA CADA servicio EN contenido.servicios: -->
                <div class="feature-card">
                    <div class="feature-icon">[servicio.icon]</div>
                    <h3>[servicio.titulo]</h3>
                    <p>[servicio.descripcion]</p>
                </div>
                <!-- FIN REPETIR -->
            </div>
        </div>
    </section>

    <!-- PROCESO (SOLO SI contenido.proceso TIENE DATOS) -->
    <section class="section" style="background:#f8f9fa;">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Cómo Funciona</span>
                <h2 class="section-title">Proceso Simple</h2>
            </div>
            <div class="process-grid">
                <!-- REPETIR PARA CADA paso EN contenido.proceso: -->
                <div class="process-step">
                    <div class="process-num">[paso.paso]</div>
                    <h3>[paso.titulo]</h3>
                    <p>[paso.descripcion]</p>
                </div>
                <!-- FIN REPETIR -->
            </div>
        </div>
    </section>

    <!-- ABOUT -->
    <section id="nosotros" class="section">
        <div class="container">
            <div class="about-grid">
                <div class="about-content">
                    <span class="section-badge">Sobre Nosotros</span>
                    <h2>[contenido.about.titulo O "¿Por Qué Elegirnos?"]</h2>
                    <p>[contenido.about.descripcion]</p>
                    <ul class="about-list">
                        <!-- REPETIR PARA CADA highlight EN contenido.about.highlights: -->
                        <li>[highlight]</li>
                        <!-- FIN REPETIR -->
                    </ul>
                    <a href="https://wa.me/[WHATSAPP_SOLO_NUMEROS]" class="btn btn-primary">Contáctanos →</a>
                </div>
                <div>
                    <img src="https://images.unsplash.com/photo-1557804506-669a67965ba0?w=600&h=400&fit=crop" alt="[nombre_negocio]" class="about-img">
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIOS (SOLO SI contenido.testimonios TIENE DATOS, SI NO OMITIR TODA LA SECCIÓN) -->
    <section class="section" style="background:#f8f9fa;">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Testimonios</span>
                <h2 class="section-title">Lo Que Dicen Nuestros Clientes</h2>
                <p class="section-subtitle">[google_data.rating]★ en Google · [google_data.total_reviews] reseñas</p>
            </div>
            <div class="testimonials-grid">
                <!-- REPETIR PARA CADA testimonio EN contenido.testimonios: -->
                <div class="testimonial">
                    <div class="testimonial-stars">★★★★★</div>
                    <p class="testimonial-text">"[testimonio.texto]"</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">[PRIMERA LETRA DE testimonio.autor]</div>
                        <div>
                            <div class="testimonial-name">[testimonio.autor]</div>
                            <div class="testimonial-role">[testimonio.rol]</div>
                        </div>
                    </div>
                </div>
                <!-- FIN REPETIR -->
            </div>
        </div>
    </section>

    <!-- FAQ (SOLO SI contenido.faqs TIENE DATOS) -->
    <section class="section">
        <div class="container">
            <div class="faq-grid">
                <div class="faq-header">
                    <span class="section-badge">FAQ</span>
                    <h2>Preguntas Frecuentes</h2>
                    <p>Resolvemos tus dudas</p>
                    <a href="https://wa.me/[WHATSAPP_SOLO_NUMEROS]" class="btn btn-primary" style="margin-top:16px;">Más Preguntas →</a>
                </div>
                <div>
                    <!-- REPETIR PARA CADA faq EN contenido.faqs: -->
                    <div class="faq-item">
                        <div class="faq-q">[faq.pregunta]</div>
                        <div class="faq-a">[faq.respuesta]</div>
                    </div>
                    <!-- FIN REPETIR -->
                </div>
            </div>
        </div>
    </section>

    <!-- CTA FINAL -->
    <section class="cta">
        <div class="container">
            <h2>[contenido.cta_final.titulo O "¿Listo Para Comenzar?"]</h2>
            <p>[contenido.cta_final.subtitulo O "Contáctanos hoy y descubre cómo podemos ayudarte"]</p>
            <div class="cta-buttons">
                <a href="https://wa.me/[WHATSAPP_SOLO_NUMEROS]" class="btn btn-white">💬 WhatsApp Directo</a>
                <a href="tel:[contacto.telefono]" class="btn btn-secondary" style="border-color:white;color:white;">📞 Llamar Ahora</a>
            </div>
        </div>
    </section>

    <!-- CONTACTO -->
    <section id="contacto" class="contact">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-form">
                    <h3>Envíanos un Mensaje</h3>
                    <form>
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" placeholder="Tu nombre" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" placeholder="tu@email.com" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" placeholder="Tu teléfono">
                        </div>
                        <div class="form-group">
                            <label>Mensaje</label>
                            <textarea rows="4" placeholder="¿En qué podemos ayudarte?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;">Enviar Mensaje →</button>
                    </form>
                </div>
                <div class="contact-info">
                    <h3>Información de Contacto</h3>
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div>[info_negocio.ciudad], [info_negocio.pais]</div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div>[contacto.telefono]</div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">💬</div>
                        <div>WhatsApp: [contacto.whatsapp]</div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">🕐</div>
                        <div>[info_negocio.horarios O "Lunes a Viernes 9am-6pm"]</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo">
                        <span class="logo-icon">[diseno.emoji_logo]</span>
                        <span class="logo-text">[nombre_negocio]</span>
                    </div>
                    <p>Profesionales comprometidos con la excelencia en [info_negocio.ciudad].</p>
                </div>
                <div>
                    <h4>Enlaces</h4>
                    <ul>
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#servicios">Servicios</a></li>
                        <li><a href="#nosotros">Nosotros</a></li>
                        <li><a href="#contacto">Contacto</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Servicios</h4>
                    <ul>
                        <!-- LISTAR 3-4 servicios principales -->
                        <li><a href="#servicios">[servicio1.titulo]</a></li>
                        <li><a href="#servicios">[servicio2.titulo]</a></li>
                        <li><a href="#servicios">[servicio3.titulo]</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Contacto</h4>
                    <ul>
                        <li><a href="tel:[contacto.telefono]">📞 [contacto.telefono]</a></li>
                        <li><a href="https://wa.me/[WHATSAPP_SOLO_NUMEROS]">💬 WhatsApp</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2024 [nombre_negocio]. Todos los derechos reservados.</p>
                <div class="social-links">
                    <!-- SI HAY contacto.instagram -->
                    <a href="https://instagram.com/[contacto.instagram SIN @]" target="_blank">📷</a>
                    <!-- SI HAY contacto.facebook -->
                    <a href="https://facebook.com/[contacto.facebook]" target="_blank">👍</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- WHATSAPP FLOTANTE -->
    <a href="https://wa.me/[WHATSAPP_SOLO_NUMEROS]?text=Hola%20vi%20su%20página%20web%20y%20me%20interesa%20información" class="wa-float" target="_blank">💬</a>

</body>
</html>
```

---

## FIN DEL TEMPLATE

## RECORDATORIO FINAL PARA CLAUDE:

1. Reemplaza TODOS los [campos] con datos reales del JSON
2. Para WHATSAPP_SOLO_NUMEROS: quita +, espacios y guiones del teléfono
3. Si una sección no tiene datos (testimonios, stats, proceso vacíos), ELIMÍNALA completamente
4. El output debe ser HTML válido que funcione al abrirlo en un navegador
5. NO incluyas ``` ni explicaciones, SOLO el HTML
