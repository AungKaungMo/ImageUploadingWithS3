# Laravel Image Processing, S3 Upload, and Role Management

This project is a Laravel application that provides functionality for:

- **Image Uploading**: Allows users to upload images through a web form or API endpoint.
- **Image Resizing**: Automatically resizes uploaded images to various dimensions.
- **Image Zipping**: Compresses multiple images into a zip file for easy download.
- **AWS S3 Upload**: Uploads processed images to an AWS S3 bucket for scalable storage.
- **User Authentication**: Implements secure API authentication using Laravel Sanctum.
- **Role and Permission Management**: Utilizes Spatie's Laravel Permission package to manage user roles and permissions.

## Prerequisites

Before setting up the project, ensure you have the following installed:

- PHP 8.0+
- Composer
- MySQL or compatible database
- AWS S3 credentials (access key, secret key, region, and bucket)

## Setup Instructions

1. **Clone the repository** and navigate into the project directory.
2. **Install dependencies** using Composer and npm (if applicable).
3. **Set up environment variables** in the `.env` file, including AWS S3 and database configurations.
4. **Run migrations** to set up the database tables, including roles and permissions.
5. **Set up storage links** if needed for file storage.
6. **Start the application** using the Laravel development server.

## Contribution

Feel free to contribute to this project by submitting pull requests or reporting issues.

## License

This project is open-source and licensed under the [MIT license](LICENSE).
