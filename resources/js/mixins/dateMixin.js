export default {
    methods: {
        /**
         * Format date to DD-MM-YYYY for display
         */
        formatDate(date) {
            if (!date) return null;
            const d = new Date(date);
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${day}-${month}-${d.getFullYear()}`;
        },

        /**
         * Convert date from DD-MM-YYYY to YYYY-MM-DD for input fields
         */
        toInputDate(date) {
            if (!date) return '';
            // If already in YYYY-MM-DD format, return as is
            if (date.match(/^\d{4}-\d{2}-\d{2}$/)) {
                return date;
            }
            // If in DD-MM-YYYY format, convert to YYYY-MM-DD
            if (date.match(/^\d{2}-\d{2}-\d{4}$/)) {
                const parts = date.split('-');
                return `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
            return date;
        },

        /**
         * Convert date from YYYY-MM-DD to DD-MM-YYYY for storage
         */
        fromInputDate(date) {
            if (!date) return null;
            // If in YYYY-MM-DD format, keep it (backend expects this format)
            if (date.match(/^\d{4}-\d{2}-\d{2}$/)) {
                return date;
            }
            return date;
        },

        /**
         * Format date for display in short format
         */
        formatDateShort(date) {
            return this.formatDate(date);
        }
    }
};
