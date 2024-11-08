import java.util.*;

public class Calculator {

    // Method to convert an infix expression to postfix notation
    public static String toPostfix(String expression) 
    {
        // Holds the final postfix expression
        StringBuilder output = new StringBuilder(); 
        // Stack for operators and parentheses
        Stack<Character> operators = new Stack<>(); 
        // Tracks if the last token was an operator (for unary minus)
        boolean lastTokenWasOperator = true; 

        for (int i = 0; i < expression.length(); i++) 
        {
            char token = expression.charAt(i);

            // If the token is whitespace, skip it
            if (Character.isWhitespace(token)) 
            {
                continue;
            }

            // If the token is a digit or a decimal point, add it to the output
            if (Character.isDigit(token) || token == '.') 
            {
                output.append(token);
                lastTokenWasOperator = false;
            }
            // If the token is an opening parenthesis, push it to the stack
            else if (token == '(') 
            {
                operators.push(token);
                // Next token can be a unary minus
                lastTokenWasOperator = true; 
            }
            // If the token is a closing parenthesis, pop until an opening parenthesis is found
            else if (token == ')') 
            {
                output.append(" ");
                while (!operators.isEmpty() && operators.peek() != '(') 
                {
                    output.append(operators.pop()).append(" ");
                }
                // Remove '(' from the stack
                if (!operators.isEmpty()) 
                {
                    operators.pop(); 
                }
                lastTokenWasOperator = false;
            }
            // If the token is an operator
            else if (isOperator(token)) 
            {
                output.append(" ");

                // Handle unary minus for negative numbers
                if (token == '-' && lastTokenWasOperator) 
                {
                    // Add a zero for cases like -3 -> 0 - 3
                    output.append("0 "); 
                } 
                else 
                {
                    // Pop operators with higher or equal precedence
                    while (!operators.isEmpty() && precedence(token) <= precedence(operators.peek())) {
                        output.append(operators.pop()).append(" ");
                    }
                }

                // Push the operator onto the stack
                operators.push(token);
                lastTokenWasOperator = true;
            }
        }

        // Pop all remaining operators in the stack
        output.append(" ");
        while (!operators.isEmpty()) 
        {
            output.append(operators.pop()).append(" ");
        }

        return output.toString().trim();
    }

    // Method to evaluate a postfix expression
    public static double evaluatePostfix(String postfix) 
    {
        // Stack to store operands
        Stack<Double> values = new Stack<>(); 

        for (String token : postfix.split("\\s+")) 
        {
            if (isNumeric(token)) 
            {
                values.push(Double.parseDouble(token));
            } 
            else if (isOperator(token.charAt(0))) 
            {
                double b = values.pop();
                double a = values.pop();
                values.push(applyOperator(token.charAt(0), a, b));
            }
        }

        // The final result is the only value remaining in the stack
        return values.pop();
    }

    // Helper method to apply an operator to two operands
    private static double applyOperator(char operator, double a, double b) 
    {
        switch (operator) 
        {
            case '+': return a + b;
            case '-': return a - b;
            case '*': return a * b;
            case '/': return a / b;
            case '^': return Math.pow(a, b);
            default: throw new IllegalArgumentException("Unknown operator: " + operator);
        }
    }

    // Method to check if a character is an operator
    private static boolean isOperator(char token) 
    {
        return token == '+' || token == '-' || token == '*' || token == '/' || token == '^';
    }

    // Method to define operator precedence
    private static int precedence(char operator) 
    {
        switch (operator) 
        {
            case '+': case '-': return 1;
            case '*': case '/': return 2;
            case '^': return 3;
            default: return -1;
        }
    }

    // Method to check if a string is numeric
    private static boolean isNumeric(String str) 
    {
        try 
        {
            Double.parseDouble(str);
            return true;
        } catch (NumberFormatException e) 
        {
            return false;
        }
    }

    public static void main(String[] args) 
    {
        String expression = "-3 + 4 * 2 / ( 1 - -5 ) ^ 2 ^ 3";
        
        // Step 1: Convert infix expression to postfix notation
        String postfix = toPostfix(expression);
        System.out.println("Postfix: " + postfix);

        // Step 2: Evaluate the postfix expression
        double result = evaluatePostfix(postfix);
        System.out.println("Result: " + result);
    }
}