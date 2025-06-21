# WP Smart Insights – Content Intelligence & UX Heatmap

A comprehensive WordPress plugin that combines AI-driven content analysis, user engagement heatmaps, and SEO scoring in one dashboard to help site owners optimize content quality and user experience.

## Features

### 🧠 AI-Powered Content Analysis
- **Readability Scoring**: Analyze content complexity and reading level
- **Sentiment Analysis**: Determine emotional tone of content
- **Tone Detection**: Identify formal, casual, or technical writing styles
- **Keyword Density Analysis**: Optimize keyword usage without overstuffing
- **Repetition Detection**: Find and fix repetitive content patterns
- **Content Recommendations**: Get actionable suggestions for improvement

### 🔥 Visual Heatmaps
- **Click Tracking**: See where users click most frequently
- **Scroll Depth Analysis**: Understand how far users scroll
- **Hover Patterns**: Track mouse movement and hover behavior
- **Real-time Data**: No external tools required
- **Visual Overlay**: Canvas-based heatmap visualization
- **UX Warnings**: Automatic detection of potential UX issues

### 📊 SEO Intelligence
- **Heading Structure Analysis**: Ensure proper H1-H6 hierarchy
- **Meta Tag Optimization**: Check title, description, and keywords
- **Internal Linking**: Analyze link structure and suggestions
- **Image Alt Tags**: Verify accessibility and SEO compliance
- **Quick Fixes**: One-click solutions for common SEO issues
- **Overall SEO Score**: Comprehensive scoring system

### 🎯 User Journey Playback
- **Anonymous Recording**: Track user interactions without privacy concerns
- **Session Playback**: Replay user sessions step-by-step
- **Interaction Logging**: Detailed mouse movements, clicks, and form interactions
- **Behavior Analysis**: Understand user patterns and pain points
- **Privacy-First Design**: No personal data collection

### 🔒 Privacy & Compliance
- **GDPR Compliant**: Full compliance with European privacy regulations
- **Cookie Consent**: Built-in consent management system
- **Data Anonymization**: All data is anonymized by default
- **No Third-Party Tracking**: Everything stays on your server
- **Data Export/Deletion**: Full control over user data
- **Privacy Settings**: Granular control over data collection

## Installation

1. **Upload the Plugin**
   - Download the plugin files
   - Upload to `/wp-content/plugins/wp-smart-insights/`
   - Or install via WordPress admin → Plugins → Add New → Upload Plugin

2. **Activate the Plugin**
   - Go to WordPress admin → Plugins
   - Find "WP Smart Insights" and click "Activate"

3. **Configure Settings**
   - Navigate to Smart Insights → Settings
   - Configure tracking options and privacy settings
   - Add AI API key for enhanced content analysis (optional)

## Usage

### Dashboard Overview
The main dashboard provides:
- **Overall Statistics**: Total posts, analyzed content, heatmap data, user journeys
- **Average Scores**: Content quality and SEO performance metrics
- **Quick Actions**: Analyze all content, export data, privacy checks
- **System Status**: Health checks and compliance status

### Content Analysis
1. Go to **Smart Insights → Content Analysis**
2. Select a post from the dropdown
3. Click **"Analyze Content"**
4. Review scores and recommendations
5. Apply suggested improvements

### Heatmap Tracking
1. Navigate to **Smart Insights → Heatmaps**
2. Select a post with tracking data
3. Choose heatmap type (clicks, scrolls, hovers)
4. View visual heatmap overlay
5. Review UX warnings and statistics

### SEO Checker
1. Visit **Smart Insights → SEO Checker**
2. Select a post to analyze
3. Review SEO scores and metrics
4. Apply quick fixes for optimization
5. Monitor improvement over time

### User Journeys
1. Go to **Smart Insights → User Journeys**
2. Filter by post and date range
3. Select a journey to replay
4. Use playback controls to analyze behavior
5. Review interaction statistics

### Settings Configuration
Configure the plugin in **Smart Insights → Settings**:

#### Tracking Settings
- Enable/disable tracking
- Configure heatmap and journey tracking
- Set data retention periods

#### AI Integration
- Add API key for enhanced analysis
- Test connection status
- Configure analysis options

#### Privacy Compliance
- Enable GDPR compliance
- Configure cookie consent
- Set data anonymization levels
- Manage data export/deletion

## Technical Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher
- **Memory**: 128MB minimum (256MB recommended)
- **Storage**: 50MB for plugin files + data storage

## Database Tables

The plugin creates the following tables:
- `wp_wpsi_heatmaps`: Stores heatmap interaction data
- `wp_wpsi_user_journeys`: Stores user journey recordings

## Privacy & Security

### Data Collection
- **Anonymous Only**: No personal information is collected
- **Session-Based**: Data is tied to anonymous session IDs
- **Local Storage**: All data stored on your server
- **Consent Required**: Users must opt-in before tracking

### Data Protection
- **Encryption**: Sensitive data is encrypted
- **Anonymization**: All data is anonymized by default
- **Retention**: Configurable data retention periods
- **Export/Delete**: Full user data control

### GDPR Compliance
- **Consent Management**: Built-in consent collection
- **Right to Access**: Users can request their data
- **Right to Deletion**: Users can request data deletion
- **Transparency**: Clear privacy policy integration

## API Integration

### AI Content Analysis
For enhanced content analysis, you can integrate with AI services:
- OpenAI GPT models
- Google Natural Language API
- Custom AI endpoints

### Webhook Support
Configure webhooks for:
- New user journey recordings
- Content analysis completion
- SEO score updates
- Privacy compliance alerts

## Troubleshooting

### Common Issues

**Tracking Not Working**
- Check if tracking is enabled in settings
- Verify user consent is given
- Check browser console for JavaScript errors
- Ensure no ad blockers are interfering

**Heatmaps Not Displaying**
- Verify post has tracking data
- Check canvas element is present
- Ensure JavaScript is loading properly
- Clear browser cache

**AI Analysis Failing**
- Verify API key is correct
- Check API service status
- Ensure sufficient API credits
- Review error logs

### Performance Optimization

**Database Optimization**
- Regular cleanup of old data
- Optimize database tables
- Monitor query performance
- Archive old records

**Memory Management**
- Limit concurrent tracking sessions
- Optimize data storage
- Monitor server resources
- Configure appropriate limits

## Support

### Documentation
- [User Guide](https://example.com/docs)
- [Developer API](https://example.com/api)
- [Privacy Policy](https://example.com/privacy)

### Support Channels
- **Email**: support@example.com
- **Forum**: [WordPress.org Support](https://wordpress.org/support/)
- **Documentation**: [Full Documentation](https://example.com/docs)

## Changelog

### Version 1.0.0
- Initial release
- AI-powered content analysis
- Visual heatmap tracking
- SEO intelligence system
- User journey playback
- Privacy-first design
- GDPR compliance

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- **Development**: Your Name
- **Design**: Modern WordPress UI patterns
- **Privacy**: GDPR-compliant architecture
- **Testing**: WordPress community

## Contributing

We welcome contributions! Please see our [Contributing Guidelines](https://example.com/contributing) for details.

---

**WP Smart Insights** - Making WordPress content optimization smarter and more privacy-focused. 