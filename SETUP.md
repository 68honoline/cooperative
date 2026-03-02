# UMUHUZA Cooperative Management System - Setup Instructions

## Running the Application with XAMPP

### Step 1: Copy Files to XAMPP
1. Go to your XAMPP installation: `C:\xampp\`
2. Navigate to `C:\xampp\htdocs\`
3. Create a new folder called `cooperative`
4. Copy ALL files from `C:\Users\Student\Documents\cooperative\` to `C:\xampp\htdocs\cooperative\`

### Step 2: Start XAMPP
1. Open **XAMPP Control Panel** (search for it in Start Menu)
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**

### Step 3: Access the Application
Open your web browser and go to: `http://localhost/cooperative/login.php`

### Default Login Credentials
- **Username:** admin
- **Password:** admin123

---

## Making Changes and Updating GitHub

### When you make code changes:
1. The changes are saved in your local files at `C:\Users\Student\Documents\cooperative\`
2. To update GitHub, run these commands in terminal:
   ```bash
   cd C:\Users\Student\Documents\cooperative
   git add .
   git commit -m "Your description of changes"
   git push
   ```

---

## Project Features
- ✅ Login/Registration with session-based authentication
- ✅ Members CRUD (Create, Read, Update, Delete)
- ✅ Products CRUD
- ✅ Clients CRUD
- ✅ Sales tracking
- ✅ Reports and summaries
- ✅ Search functionality

---

## Troubleshooting

### If you get a database error:
1. Make sure MySQL is running in XAMPP
2. The database `cooperative_db` will be created automatically on first login

### If Apache won't start:
1. Check if another application is using port 80
2. Try clicking "Config" next to Apache in XAMPP, then "httpd.conf" and change port 80 to 8080
3. Access via `http://localhost:8080/cooperative/login.php`
