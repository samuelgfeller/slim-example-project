
/**
 * Determine avatar path with sex
 *
 * @param {string} sex
 */
export function getAvatarPath(sex){
    switch(sex){
        case 'M':
            return "assets/client/img/avatar_male.svg";
        case 'F':
            return "assets/client/img/avatar_female.svg";
        case 'O':
        default:
            return "assets/client/img/avatar_neutral.svg";
    }
}