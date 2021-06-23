import { mutations } from 'src/store/mutations';

// Import mock state
import { state } from 'src/store/__mocks__/index';

// Destructure assign `mutations`
const { setCenterToast } = mutations;

describe('setCenterToast mutation', () => {
  it('Expects no CenterToast to be visible', () => {
    setCenterToast(state, {
      show: false,
    });
    // assert result
    expect(state.centerToast.status).toBeFalsy();
  });

  it('Expects CenterToast to be visible', () => {
    setCenterToast(state, {
      show: true,
      text: 'Test',
    });
    // assert result
    expect(state.centerToast.show).toBeTruthy();
    expect(state.centerToast.text).toBeDefined();
  });
});
