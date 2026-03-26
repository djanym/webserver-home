Future Features (Planned)

These features may be implemented later:
•	Project export
•	Project backup
•	Project duplication
•	SSL (local certificates)
•	Nginx support
•	Environment variables
•	Git integration
•	Project templates


# Project Creation Workflow

User inputs:

- Project name
- Slug
- Domain
- Metadata

App automatically:

- Create project folder
- Create subfolders
- Create `project-config.php`
- Create boilerplate files
- Create Apache virtual host
- Register in `server-config.php`

-------------


---

# Import Logic

Import steps:

1. Copy project folder to server root
2. Select `project-config.php`
3. Register project

Rules:

- Import must not modify project
- Project remains portable

-----------------

UI Description - Dashboard and Project Management Interface
Main Dashboard Layout
Direct Access:
No login form or authentication screen on startup
Application loads directly to the main dashboard
Clean, immediate access to project management functionality

Dashboard Structure:
Header with application title and main action buttons
Primary content area displaying project listing
Footer with system status or quick actions (optional)

Project Listing Interface

Vertical List Layout (Total Commander Style):
Projects displayed in a vertical list format similar to file manager interfaces
Each project occupies one row with consistent spacing and alignment
Alternating row colors or subtle borders for visual separation
Sortable columns (Name, Created Date, Last Modified, Status)

Project Row Components:
Project Icon: Visual indicator (folder icon, project type icon, or custom favicon)
Project Name: Clickable project title/name (primary action)
Project Details: Subdomain, path, or other metadata displayed as secondary text
Status Indicator: Visual status (active, inactive, error) with color coding
Action Icons: Right-aligned horizontal row of action buttons

Action Icons per Project Row:
Open/Browse: Navigate to project URL or file browser
Backup: Create project archive/backup
Settings: Edit project configuration
Virtual Host: Manage Apache configuration
Delete: Remove project (with confirmation)
View error log file.
More Actions: Dropdown menu for additional options

Project Interaction Behavior

Primary Click Action (Project Name):
Option A: Open project URL in new tab/window (if virtual host is active)
Option B: Open integrated file navigator/browser within the dashboard
Option C: Show project details panel with both options available

File Navigator Integration:
Breadcrumb Navigation: Show current path within project structure
File/Folder Listing: Display contents with file icons and metadata
Context Actions: Right-click or action buttons for files/folders
Archive Functionality: Select folders/files and create ZIP archives
Upload/Download: File management capabilities
Code Preview: Basic syntax highlighting for common file types

Enhanced Project Management Features

Bulk Operations:
Checkbox selection for multiple projects
Bulk actions toolbar (backup multiple, archive, export settings)
Select all/none functionality

Search and Filtering:
Search bar for project names and metadata
Filter by status, creation date, or project type
Quick filter buttons (Active, Inactive, Recent)

Project Creation Integration:
Prominent "Create New Project" button in header or sidebar
Quick project templates or recent project cloning options

Visual Enhancements:
Project Thumbnails: Optional preview images or screenshots
Progress Indicators: Show backup progress, file operations
Status Badges: Visual indicators for project health, updates needed
Responsive Design: Adapt layout for different screen sizes

File Navigator Detailed Features

Navigation Interface:
Tree View Sidebar: Expandable folder structure (optional)
Main Content Area: File listing with details view
Toolbar: Navigation buttons, view options, action buttons

File Operations:
Create: New files, folders, from templates
Edit: Integrated code editor for common file types
Archive: Create ZIP/TAR archives of selected items
Extract: Unpack archives directly in browser
Copy/Move: File management operations
Permissions: View and modify file permissions (Unix systems)

Archive Management:
Select Multiple: Checkbox selection for files/folders
Archive Options: Choose compression level, format, exclude patterns
Download Archives: Direct download of created archives
Archive History: List of previously created backups

Project Creation Flow Description
Overview
Implement a complete project creation workflow that allows users to create new web projects through a user-friendly interface, automatically setting up the necessary folder structure, Apache virtual host configuration, and project metadata storage.
Frontend Requirements
Main Screen Integration:
Add a prominent "Create New Project" button on the main dashboard.
Button should be easily accessible and visually distinct.
Project Creation Form:
Project Title: Text input for the human-readable project name
Project Slug: Text input for URL-safe identifier (auto-generate from title with option to edit)
Subdomain: Text input for the virtual host domain (e.g., myproject.local)
Project Directory: Radio button selection between:
Default: Show read-only path preview (e.g., /Users/username/projects/[slug])
Custom: Show additional text input field for custom path
Form Validation: Client-side validation for required fields, slug format, subdomain format
Submit Button: Send AJAX request to backend API

User Experience:
Real-time slug generation from title (optional)
Path preview updates based on slug/custom selection
Loading state during submission
Success/error feedback with appropriate messaging
Form reset or redirect after successful creation

Backend Requirements




API Endpoint:
Create POST /api/projects/create endpoint
Accept JSON payload with project data
Implement input validation and sanitization
Return structured JSON response (success/error with details)

Project Creation Process:

    Validate Input: Check required fields, slug uniqueness, path availability
    Create Folder Structure:
        Main project folder
        docs/ subdirectory
        [project-slug]/ subdirectory
        Generate project-config.php file with project metadata
    Generate Virtual Host:
        Load template from /app-root/templates/default-vhost.conf
        Replace placeholders with project data
        Write to Apache vhosts directory
    Restart Apache: Execute system command to reload Apache configuration
    Update Projects Registry: Add project entry to projects.meta.json
    Return Response: Success confirmation or detailed error information

Configuration Requirements

Use configuration file for values.

<VirtualHost *:80>
ServerName {{SUBDOMAIN}}
DocumentRoot {{PROJECT_PATH}}/src
ErrorLog /usr/local/var/log/httpd/{{SLUG}}_error.log
CustomLog /usr/local/var/log/httpd/{{SLUG}}_access.log combined

    <Directory "{{PROJECT_PATH}}/src">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

Data Storage Requirements

Projects Registry (/app-root/backend/config/projects.meta.json):

{
"projects": [
{
"id": "unique-id",
"title": "My Project",
"slug": "my-project",
"subdomain": "myproject.local",
"path": "/Users/username/projects/my-project",
"created_at": "2025-01-01T12:00:00Z",
"status": "active"
}
],
"last_updated": "2025-01-01T12:00:00Z"
}

Individual Project Config (project-config.json in each project folder):

{
"project": {
"id": "unique-id",
"title": "My Project",
"slug": "my-project",
"subdomain": "myproject.local",
"created_at": "2025-01-01T12:00:00Z"
},
"settings": {
"php_version": "8.2",
"framework": null,
"database": null
}
}


Security & Error Handling

Security Measures:
Input validation and sanitization
Path traversal prevention
Permission checks before file operations
Secure command execution for Apache restart

Error Handling:
Graceful handling of file system errors
Apache configuration validation
Rollback mechanism for failed operations
Detailed error logging and user feedback

Cross-Platform Considerations

OS Detection and Configuration:
Detect operating system (PHP_OS)
Load OS-specific paths and commands from config
Support for Mac (Homebrew), Linux (systemd/service), and Windows (XAMPP/WAMP)


Future Enhancements (leave placeholders for these features for future):
User authentication and role-based permissions
Project templates and Git integration
Server monitoring and resource usage stats
SSL management and security features

