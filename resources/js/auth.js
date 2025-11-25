// Auth utility for managing user authentication and permissions

export default {
    // Get current user from localStorage
    getUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    },

    // Set user data in localStorage
    setUser(user) {
        localStorage.setItem('user', JSON.stringify(user));
    },

    // Get token from localStorage
    getToken() {
        return localStorage.getItem('token');
    },

    // Set token in localStorage
    setToken(token) {
        localStorage.setItem('token', token);
    },

    // Check if user is authenticated
    isAuthenticated() {
        return !!this.getToken();
    },

    // Logout user
    logout() {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
    },

    // Get user permissions
    getPermissions() {
        const user = this.getUser();
        return user && user.permissions ? user.permissions : [];
    },

    // Get user roles
    getRoles() {
        const user = this.getUser();
        return user && user.roles ? user.roles : [];
    },

    // Check if user has a specific permission
    can(permission) {
        const permissions = this.getPermissions();
        return permissions.includes(permission);
    },

    // Check if user has any of the given permissions
    canAny(permissionsArray) {
        const userPermissions = this.getPermissions();
        return permissionsArray.some(permission => userPermissions.includes(permission));
    },

    // Check if user has all of the given permissions
    canAll(permissionsArray) {
        const userPermissions = this.getPermissions();
        return permissionsArray.every(permission => userPermissions.includes(permission));
    },

    // Check if user has a specific role
    hasRole(role) {
        const roles = this.getRoles();
        return roles.includes(role);
    },

    // Check if user has any of the given roles
    hasAnyRole(rolesArray) {
        const userRoles = this.getRoles();
        return rolesArray.some(role => userRoles.includes(role));
    }
};
