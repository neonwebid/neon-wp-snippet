/**
 * @file NeonDependencyManager class for handling form element dependencies.
 *
 * HTML Form Example usage:
 * <code>
 *     <div class="neon-field-item">
 *       <div class="form-check">
 *         <input class="form-check-input" type="checkbox" value="yes" id="flexCheckDefault">
 *         <label class="form-check-label" for="flexCheckDefault">
 *             Default checkbox
 *         </label>
 *       </div>
 *
 *       <select class="form-select" id="selectDropdown">
 *         <option value="">Choose an option</option>
 *         <option value="1">Option 1</option>
 *         <option value="2">Option 2</option>
 *         <option value="3">Option 3</option>
 *       </select>
 *
 *       <div id="email-data" class="mb-3" data-dependency='[["flexCheckDefault", "==", "yes"], "and", ["selectDropdown", "==", "2"]]'>
 *         <label for="emailInput" class="form-label">Email address</label>
 *         <input type="email" class="form-control" id="emailInput" placeholder="name@example.com">
 *       </div>
 *      </div>
 * </code>
 *
 *
 * Example usage:
 * <code>
 *     new NeonDependencyManager({selector: '.neon-field-item'});
 * </code>
 *
 * Define supported operators for dependency evaluation.
 * @type {Object}
 * @property {function(*, *): boolean} '==' - Checks if two values are equal.
 * @property {function(*, *): boolean} '!=' - Checks if two values are not equal.
 * @property {function(*, *): boolean} '>=' - Checks if the first value is greater than or equal to the second value.
 * @property {function(*, *): boolean} '<=' - Checks if the first value is less than or equal to the second value.
 * @property {function(*, Array): boolean} 'in' - Checks if the first value is included in the array.
 * @property {function(*, Array): boolean} 'not_in' - Checks if the first value is not included in the array.
 * @property {function(*): boolean} 'is_empty' - Checks if the value is empty or null.
 * @property {function(*): boolean} 'is_not_empty' - Checks if the value is not empty or null.
 * @property {function(*, *): boolean} 'starts_with' - Checks if the value starts with the specified string.
 * @property {function(*, *): boolean} 'contains' - Checks if the value contains the specified string.
 * @property {function(*, *): boolean} 'ends_with' - Checks if the value ends with the specified string.
 */
class NeonDependencyManager {
    constructor(options) {
        this.selector = options.selector || '.neon-field-item';
        this.operators = {
            '==': (a, b) => a === b,
            '!=': (a, b) => a !== b,
            '>=': (a, b) => a >= b,
            '<=': (a, b) => a <= b,
            'in': (a, b) => Array.isArray(b) && b.includes(a),
            'not_in': (a, b) => Array.isArray(b) && !b.includes(a),
            'is_empty': a => a === '' || a === null,
            'is_not_empty': a => a !== '' && a !== null,
            'starts_with': (a, b) => typeof a === 'string' && a.startsWith(b),
            'contains': (a, b) => typeof a === 'string' && a.includes(b),
            'ends_with': (a, b) => typeof a === 'string' && a.endsWith(b)
        };

        // Bind methods
        this.checkCondition = this.checkCondition.bind(this);
        this.evaluateConditions = this.evaluateConditions.bind(this);
        this.applyDependencies = this.applyDependencies.bind(this);

        // Initialize
        this.init();
    }

    /**
     * Checks if a single condition is met based on the field, operator, and value.
     * @param {Array} condition - An array containing the field ID, operator, and value.
     * @returns {boolean} - Returns true if the condition is met, false otherwise.
     */
    checkCondition([field, operator, value]) {
        const element = document.getElementById(field);

        if (!element) {
            console.error(`Element with ID "${field}" not found`);
            return false;
        }

        let fieldValue;

        if (element.type === 'checkbox') {
            // Handle checkbox
            fieldValue = element.checked ? element.value : '';
        } else if (element.type === 'radio') {
            // Handle radio buttons
            const radios = document.querySelectorAll(`input[name="${element.name}"]`);
            radios.forEach(radio => {
                if (radio.checked) {
                    fieldValue = radio.value;
                }
            });
        } else if (element.tagName.toLowerCase() === 'select') {
            // Handle select dropdown
            fieldValue = element.value;
        } else {
            // Handle other input types (e.g., text, email, etc.)
            fieldValue = element.value;
        }

        // Execute the appropriate operator function
        return this.operators[operator](fieldValue, value);
    }

    /**
     * Evaluates a set of conditions to determine if they are met.
     * @param {Array} conditions - An array of conditions, logical operators, and values.
     * @returns {boolean} - Returns true if all conditions are met according to the logical operators.
     */
    evaluateConditions(conditions) {
        if (Array.isArray(conditions[0])) {
            let result = this.checkCondition(conditions[0]);

            for (let i = 1; i < conditions.length; i += 2) {
                const logical = conditions[i]; // should be 'and' or 'or'
                const nextCondition = this.checkCondition(conditions[i + 1]);

                if (logical === 'and') {
                    result = result && nextCondition;
                } else if (logical === 'or') {
                    result = result || nextCondition;
                }
            }
            return result;
        }
        // Single condition case
        return this.checkCondition(conditions);
    }

    /**
     * Applies the dependency rules to elements with the 'data-dependency' attribute.
     */
    applyDependencies() {
        const dependentElements = document.querySelectorAll('[data-dependency]');

        dependentElements.forEach(el => {
            const dependencyData = el.getAttribute('data-dependency');
            const conditions = JSON.parse(dependencyData); // Parse JSON string to array

            if (this.evaluateConditions(conditions)) {
                el.style.display = ''; // Show element if conditions are met
            } else {
                el.style.display = 'none'; // Hide element if conditions are not met
            }
        });
    }

    /**
     * Initializes the dependency manager by setting up event listeners and applying initial dependencies.
     */
    init() {
        // Listen for changes in inputs within elements having class selector
        const inputs = document.querySelectorAll(`${this.selector} input, ${this.selector} select, ${this.selector} textarea`);
        inputs.forEach(input => {
            input.addEventListener('change', this.applyDependencies);
        });

        // Initial check when the page is loaded
        this.applyDependencies();
    }
}

// Instantiation
new NeonDependencyManager();
