# Win80x Landing Page

A modern, fully responsive landing page for the Win80x gaming app built with Bootstrap 5.

## Features

- **Fully Responsive Design**: Works perfectly on mobile, tablet, and desktop devices
- **Modern UI/UX**: Clean, professional design with smooth animations
- **Bootstrap 5**: Built with the latest Bootstrap framework (no jQuery required)
- **Custom Color Scheme**: Primary color (#667eea) with cream white accents
- **Interactive Elements**: Smooth scrolling, hover effects, and modal dialogs
- **Accessibility**: Focus states, keyboard navigation, and screen reader support
- **Performance Optimized**: Lazy loading, debounced events, and efficient animations

## File Structure

```
win80x/
├── index.html          # Main HTML file
├── styles.css          # Custom CSS styles
├── script.js           # JavaScript functionality
└── README.md           # This file
```

## Sections Included

1. **Navigation Bar**: Fixed header with smooth scrolling navigation
2. **Hero Section**: Eye-catching banner with download CTA
3. **About Section**: Brief description with feature highlights
4. **Features Section**: Detailed feature list with icons
5. **Screenshots Section**: App preview images (placeholder)
6. **FAQ Section**: Expandable accordion with common questions
7. **Contact Section**: Support information and contact details
8. **Footer**: Links, social media icons, and additional information

## Customization

### Colors

The color scheme is defined in CSS variables at the top of `styles.css`:

```css
:root {
  --primary-color: #667eea; /* Main brand color */
  --primary-dark: #5a6fd8; /* Darker shade for hover */
  --accent-color: #f8f6f0; /* Cream white background */
  --text-dark: #2d3748; /* Main text color */
  --text-muted: #718096; /* Secondary text color */
}
```

### Content

- Update text content directly in `index.html`
- Replace placeholder images with actual screenshots
- Modify FAQ questions and answers in the accordion section
- Update contact information and social media links

### Images

Replace the placeholder image URLs in the following locations:

- Hero section: Main app showcase image
- Screenshots section: Four app screenshot images
- Consider using actual screenshots of your app

### Download Functionality

The download buttons currently show a modal dialog. To implement actual downloads:

1. Replace `#` href attributes with actual APK download URLs
2. Modify the `startDownload()` function in `script.js`
3. Consider implementing download tracking analytics

## Browser Support

- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari 12+, Chrome Mobile 60+)

## Dependencies

- **Bootstrap 5.3.0**: CSS framework
- **Bootstrap Icons**: Icon library
- **Google Fonts (Inter)**: Typography
- **Vanilla JavaScript**: No additional libraries required

## Performance Features

- **Lazy Loading**: Images load as they come into view
- **Debounced Scroll Events**: Optimized scroll performance
- **CSS Animations**: Hardware-accelerated animations
- **Reduced Motion Support**: Respects user accessibility preferences
- **Minified Resources**: External resources loaded from CDN

## Accessibility Features

- **Semantic HTML**: Proper heading hierarchy and landmarks
- **Keyboard Navigation**: All interactive elements are keyboard accessible
- **Focus Indicators**: Visible focus states for navigation
- **Screen Reader Support**: ARIA labels and descriptions
- **High Contrast Support**: Adapts to user's contrast preferences
- **Reduced Motion**: Respects prefers-reduced-motion setting

## SEO Considerations

- **Meta Tags**: Includes viewport and charset meta tags
- **Semantic Structure**: Proper HTML5 semantic elements
- **Alt Text**: Placeholder alt text for images (update with actual descriptions)
- **Page Title**: Descriptive title tag

## Mobile Optimization

- **Touch-Friendly**: Buttons and links sized for touch interaction
- **Responsive Images**: Images scale appropriately on all devices
- **Mobile Navigation**: Collapsible navbar for mobile devices
- **Fast Loading**: Optimized for mobile networks

## Getting Started

1. **Download/Clone** the files to your web server
2. **Update Content**: Modify text, images, and links as needed
3. **Test Responsiveness**: Check on various device sizes
4. **Deploy**: Upload to your web hosting service

## Customization Tips

### Adding New Sections

1. Add the HTML structure following Bootstrap 5 patterns
2. Include proper spacing classes (`py-5`, `mb-4`, etc.)
3. Add navigation link in the navbar
4. Update the smooth scrolling JavaScript if needed

### Changing Animations

- Modify CSS animations in `styles.css`
- Adjust JavaScript animation triggers in `script.js`
- Use `prefers-reduced-motion` for accessibility

### Adding Forms

- Use Bootstrap 5 form components
- Add validation using JavaScript
- Consider integrating with email services for contact forms

## Support

For questions about customization or implementation, refer to:

- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.3/)
- [Bootstrap Icons](https://icons.getbootstrap.com/)
- [CSS Grid and Flexbox Guides](https://css-tricks.com/)

## License

This landing page template is provided as-is for use with the Win80x project. Feel free to modify and customize as needed for your application.

---

**Note**: Remember to replace all placeholder content, images, and links with your actual app information before going live.
