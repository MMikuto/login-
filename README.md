# project_login

## Leave system accounts

The system has separate User and Admin roles. Users can submit leave requests and view only their own history. Admins can view and approve or reject requests.

For an existing database that already has `admin` and `leave_requests` tables, import `php_api/date/role_access.sql` once. It creates the `users` table, adds ownership and review fields to leave requests, and creates a sample User account:

- User: `Mikuto` / `25222500`
- Admin: `useradmin` / `25222522`

The sample User password is stored as a password hash. Change the sample credentials before deploying outside a development environment.

For a fresh database, import `php_api/date/admin.sql`, then `php_api/date/leave_requests.sql`, then `php_api/date/role_access.sql`.

## Project setup
```
npm install
```

### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```

### Customize configuration
See [Configuration Reference](https://cli.vuejs.org/config/).
