import { describe, expect, it } from 'vitest'
import { messageReplyPayload } from '../familyRules.ts'

describe('family message monitor', () => {
  it('asserts_reply_drawer_sends_thread_id_and_student_id', () => {
    expect(messageReplyPayload({ student_id: 12, thread_id: 'student-12' }, 'Please check homework')).toEqual({
      student_id: 12,
      thread_id: 'student-12',
      content: 'Please check homework',
    })
  })
})
